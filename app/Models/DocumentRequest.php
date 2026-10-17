<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'tracking_number',
        'last_name',
        'first_name',
        'middle_initial',
        'contact_number',
        'resident_email',
        'purok',
        'street',
        'city',
        'age',
        'document_type_id',
        'document_type',
        'id_type',
        'documents',
        'attachments',
        'priority',
        'purpose',
        'fee',
        'status',
        'is_generated',
        'assigned_staff',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'document_type_id' => 'integer',
        'documents'        => 'array',
        'attachments'      => 'array',
        'is_generated'     => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saving(function ($request) {
            // Keep document_type string and document_type_id in sync
            if (!empty($request->document_type_id) && empty($request->document_type)) {
                $docType = DocumentType::find($request->document_type_id);
                if ($docType) {
                    $request->document_type = $docType->name;
                }
            } elseif (!empty($request->document_type) && empty($request->document_type_id)) {
                $matchedId = DocumentType::where('name', $request->document_type)->value('id');
                if ($matchedId) {
                    $request->document_type_id = $matchedId;
                }
            }
        });
    }

    /**
     * Relationship to the resident user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship to the document type.
     */
    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    /**
     * Get the normalized list of attached/supporting documents.
     */
    public function getAttachmentsListAttribute(): array
    {
        $list = [];

        // 1. Direct attachments uploaded with this request
        $att = $this->attachments;
        if (is_string($att)) {
            $att = json_decode($att, true);
        }
        if (is_array($att)) {
            foreach ($att as $item) {
                if (is_array($item) && !empty($item['file_path'])) {
                    $ext = strtolower(pathinfo($item['file_path'], PATHINFO_EXTENSION));
                    $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                    $list[] = [
                        'file_path' => $item['file_path'],
                        'file_name' => $item['file_name'] ?? basename($item['file_path']),
                        'doc_type'  => $item['doc_type'] ?? 'Supporting Document',
                        'file_type' => $item['file_type'] ?? ($isImg ? 'image' : 'document'),
                        'file_size' => $item['file_size'] ?? null,
                        'source'    => 'request',
                    ];
                }
            }
        }

        // 2. Also check if the user has ID photo or proof of residency from profile
        $user = $this->user ?: ($this->user_id ? User::find($this->user_id) : null);
        if ($user) {
            if (!empty($user->id_photo)) {
                $ext = strtolower(pathinfo($user->id_photo, PATHINFO_EXTENSION));
                $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                $list[] = [
                    'file_path' => $user->id_photo,
                    'file_name' => 'Resident ID Photo (' . ($user->id_type ?: 'Valid ID') . ')',
                    'doc_type'  => $user->id_type ?: 'Valid Government ID',
                    'file_type' => $isImg ? 'image' : 'document',
                    'file_size' => null,
                    'source'    => 'profile_id',
                ];
            }
            if (!empty($user->proof_of_residency)) {
                $ext = strtolower(pathinfo($user->proof_of_residency, PATHINFO_EXTENSION));
                $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                $list[] = [
                    'file_path' => $user->proof_of_residency,
                    'file_name' => 'Proof of Residency',
                    'doc_type'  => 'Proof of Residence',
                    'file_type' => $isImg ? 'image' : 'document',
                    'file_size' => null,
                    'source'    => 'profile_residency',
                ];
            }
        }

        return $list;
    }

    /**
     * Get the resident's full name.
     */
    public function getResidentNameAttribute(): string
    {
        $mi = $this->middle_initial ? $this->middle_initial . '. ' : '';
        $name = trim("{$this->first_name} {$mi}{$this->last_name}");
        return $name ?: ($this->attributes['resident_name'] ?? '');
    }

    /**
     * Get the normalized list of documents for this request.
     * Always returns an array of document items:
     * [['type' => '...', 'purpose' => '...', 'fee' => ..., 'is_generated' => ...]]
     */
    public function getDocumentsListAttribute(): array
    {
        $docs = $this->documents;

        if (is_string($docs)) {
            $docs = json_decode($docs, true);
        }

        if (is_array($docs) && count($docs) > 0) {
            return array_map(function ($doc) {
                return [
                    'type'         => is_array($doc) ? ($doc['type'] ?? '') : (string)$doc,
                    'purpose'      => is_array($doc) ? ($doc['purpose'] ?? $this->purpose ?? '') : ($this->purpose ?? ''),
                    'fee'          => is_array($doc) ? ($doc['fee'] ?? null) : null,
                    'is_generated' => is_array($doc) ? (bool)($doc['is_generated'] ?? false) : false,
                ];
            }, $docs);
        }

        if (!empty($this->document_type)) {
            return [
                [
                    'type'         => $this->document_type,
                    'purpose'      => $this->purpose ?? '',
                    'fee'          => $this->fee,
                    'is_generated' => (bool)$this->is_generated,
                ]
            ];
        }

        return [];
    }

    /**
     * Determine if this request contains multiple documents.
     */
    public function hasMultipleDocuments(): bool
    {
        return count($this->documents_list) > 1;
    }

    /**
     * Get an array of just the document type names.
     */
    public function getDocumentTypesListAttribute(): array
    {
        return array_values(array_filter(array_column($this->documents_list, 'type')));
    }

    /**
     * Get the resolved ID type for this request.
     * Checks the request id_type, resident profile id_type, or attachments.
     */
    public function getResolvedIdTypeAttribute(): ?string
    {
        if (!empty($this->id_type)) {
            return $this->id_type;
        }

        $user = $this->user ?: ($this->user_id ? User::find($this->user_id) : null);
        if ($user && !empty($user->id_type)) {
            return $user->id_type;
        }

        if (!empty($this->attachments_list)) {
            foreach ($this->attachments_list as $att) {
                if (!empty($att['doc_type']) && (stripos($att['doc_type'], 'ID') !== false || stripos($att['doc_type'], 'License') !== false || stripos($att['doc_type'], 'Passport') !== false)) {
                    return $att['doc_type'];
                }
            }
        }

        return null;
    }
}