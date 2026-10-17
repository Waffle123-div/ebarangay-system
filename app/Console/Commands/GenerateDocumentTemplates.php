<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\SimpleType\VerticalJc;
use PhpOffice\PhpWord\Style\Font;

class GenerateDocumentTemplates extends Command
{
    protected $signature   = 'templates:generate';
    protected $description = 'Generate official .docx template files for document requests';

    public function handle(): int
    {
        $this->generateClearanceTemplate();
        $this->generateSeniorCitizenCertificationTemplate();
        $this->generateSeniorCitizenCashAssistanceTemplate();
        $this->generateBurialAssistanceTemplate();
        $this->generateBusinessClearanceTemplate();
        $this->generateElectricConnectionTemplate();
        $this->generateAcknowledgementReceiptTemplate();
        $this->generateCohabitationTemplate();
        $this->generateBarangayCertificateTemplate();
        $this->generateFirstTimeJobseekerTemplate();
        $this->generateResidencyTemplate();
        $this->generateBusinessPermitTemplate();
        $this->generateIndigencyTemplate();
        $this->generateProbationBailTemplate();
        $this->generateOathOfUndertakingTemplate();

        $this->info('All document templates generated successfully in storage/app/.');
        return 0;
    }

    // ---------------------------------------------------------------
    // Shared helpers
    // ---------------------------------------------------------------

    private function saveTo(PhpWord $phpWord, string $filename): void
    {
        $path = storage_path('app/' . $filename);
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($path);
        $this->line("  Created: {$filename}");
    }

    private function createOfficialSection(PhpWord $phpWord): \PhpOffice\PhpWord\Element\Section
    {
        $section = $phpWord->addSection([
            'marginTop'    => 1080,
            'marginBottom' => 1080,
            'marginLeft'   => 1200,
            'marginRight'  => 1200,
        ]);

        $logoPath = public_path('images/logo.png');

        // Center background watermark
        if (file_exists($logoPath)) {
            $header = $section->addHeader();
            $header->addWatermark($logoPath, [
                'width'      => 360,
                'height'     => 360,
                'marginTop'  => 180,
                'marginLeft' => 80,
            ]);
        }

        // Header layout table with logo on the left and Republic details
        $headerTable = $section->addTable([
            'borderSize'  => 0,
            'borderColor' => 'ffffff',
            'cellMargin'  => 0,
            'alignment'   => JcTable::CENTER,
        ]);
        $headerTable->addRow();

        $cellLogo = $headerTable->addCell(1600, ['valign' => VerticalJc::CENTER]);
        if (file_exists($logoPath)) {
            $cellLogo->addImage($logoPath, [
                'width'     => 75,
                'height'    => 75,
                'alignment' => Jc::CENTER,
            ]);
        }

        $cellText = $headerTable->addCell(6800, ['valign' => VerticalJc::CENTER]);
        $cellText->addText('REPUBLIC OF THE PHILIPPINES', ['name' => 'Times New Roman', 'size' => 11, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 20]);
        $cellText->addText('Province of Cebu', ['name' => 'Times New Roman', 'size' => 10.5], ['alignment' => Jc::CENTER, 'spaceAfter' => 20]);
        $cellText->addText('Municipality of Carmen', ['name' => 'Times New Roman', 'size' => 10.5], ['alignment' => Jc::CENTER, 'spaceAfter' => 20]);
        $cellText->addText('Barangay Cogon East', ['name' => 'Times New Roman', 'size' => 11, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 100]);
        $cellText->addText('OFFICE OF THE PUNONG BARANGAY', ['name' => 'Times New Roman', 'size' => 11, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 40]);

        $section->addTextBreak(1);

        // Horizontal dividing line
        $section->addLine([
            'weight' => 1.5,
            'width'  => 480,
            'height' => 0,
            'color'  => '000000',
        ]);

        $section->addTextBreak(1);

        return $section;
    }

    private function addOfficialSignaturesAndFooter(\PhpOffice\PhpWord\Element\Section $section): void
    {
        $section->addTextBreak(2);

        $table = $section->addTable([
            'borderSize'  => 0,
            'borderColor' => 'ffffff',
            'cellMargin'  => 40,
            'alignment'   => JcTable::CENTER,
        ]);

        $table->addRow();
        $cell1 = $table->addCell(5000);
        $cell1->addText('Prepared by:', ['name' => 'Times New Roman', 'size' => 11], ['spaceAfter' => 240]);
        $cell1->addTextBreak(2);
        $cell1->addText('${secretary_name}', ['name' => 'Times New Roman', 'size' => 11, 'bold' => true], ['spaceAfter' => 30]);
        $cell1->addText('Barangay Secretary', ['name' => 'Times New Roman', 'size' => 11], ['spaceAfter' => 0]);

        $cell2 = $table->addCell(4500);
        $cell2->addText('Approved by:', ['name' => 'Times New Roman', 'size' => 11], ['spaceAfter' => 240]);
        $cell2->addTextBreak(2);
        $cell2->addText('${captain_name}', ['name' => 'Times New Roman', 'size' => 11, 'bold' => true], ['spaceAfter' => 30]);
        $cell2->addText('Punong Barangay', ['name' => 'Times New Roman', 'size' => 11], ['spaceAfter' => 0]);

        $section->addTextBreak(2);

        // Footer / Metadata block (Italicized)
        $section->addText(
            'O.R. No.: ${or_number}',
            ['name' => 'Times New Roman', 'size' => 10, 'italic' => true],
            ['spaceAfter' => 20]
        );
        $section->addText(
            'Amount Paid: ₱${amount}',
            ['name' => 'Times New Roman', 'size' => 10, 'italic' => true],
            ['spaceAfter' => 20]
        );
        $section->addText(
            'Date Issued: ${date}',
            ['name' => 'Times New Roman', 'size' => 10, 'italic' => true],
            ['spaceAfter' => 20]
        );
        $section->addText(
            'Tracking Reference: ${tracking_number}',
            ['name' => 'Times New Roman', 'size' => 10, 'italic' => true],
            ['spaceAfter' => 20]
        );
    }

    private function addSingleCaptainFooter(\PhpOffice\PhpWord\Element\Section $section): void
    {
        $section->addTextBreak(2);

        // Horizontal dividing line
        $section->addLine([
            'weight' => 1.5,
            'width'  => 480,
            'height' => 0,
            'color'  => '94a3b8',
        ]);

        $section->addTextBreak(1);

        $p = $section->addTextRun(['alignment' => Jc::START]);
        $p->addText('${captain_name}', ['name' => 'Times New Roman', 'size' => 11, 'bold' => true]);
        $p->addText(' Punong Barangay', ['name' => 'Times New Roman', 'size' => 11]);

        $section->addText(
            'Tracking Reference: ${tracking_number}',
            ['name' => 'Times New Roman', 'size' => 10, 'italic' => true],
            ['alignment' => Jc::START, 'spaceBefore' => 60]
        );
    }

    private function headerSection(PhpWord $phpWord): \PhpOffice\PhpWord\Element\Section
    {
        $section = $phpWord->addSection([
            'marginTop'    => 1440,
            'marginBottom' => 1440,
            'marginLeft'   => 1440,
            'marginRight'  => 1440,
        ]);

        $section->addText(
            'Republic of the Philippines',
            ['name' => 'Times New Roman', 'size' => 11, 'bold' => false],
            ['alignment' => Jc::CENTER]
        );
        $section->addText(
            'Province of ${province}',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::CENTER]
        );
        $section->addText(
            'Municipality/City of ${city}',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::CENTER]
        );
        $section->addText(
            'BARANGAY ${barangay_name}',
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER]
        );

        $section->addTextBreak(1);

        return $section;
    }

    private function addSignatureBlock(\PhpOffice\PhpWord\Element\Section $section): void
    {
        $section->addTextBreak(3);
        $section->addText(
            'HON. ${captain}',
            ['name' => 'Times New Roman', 'size' => 12, 'bold' => true],
            ['alignment' => Jc::CENTER]
        );
        $section->addText(
            'Barangay Captain',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::CENTER]
        );
    }

    private function addOfficeCopy(\PhpOffice\PhpWord\Element\Section $section): void
    {
        $section->addTextBreak(2);
        $section->addText(
            'Doc No. _____ Page No. _____ Book No. _____ Series of ' . date('Y'),
            ['name' => 'Times New Roman', 'size' => 10],
            ['alignment' => Jc::CENTER]
        );
    }

    // ---------------------------------------------------------------
    // 1. Barangay Clearance
    // ---------------------------------------------------------------

    private function generateClearanceTemplate(): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $this->createOfficialSection($phpWord);

        $section->addText(
            'BARANGAY CLEARANCE',
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );

        $section->addTextBreak(1);

        $section->addText(
            'TO WHOM IT MAY CONCERN:',
            ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
            ['alignment' => Jc::START, 'spaceAfter' => 120]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS IS TO CERTIFY that ${name}, ${age} years old, ${civil_status}, and a Filipino citizen, is a bonafide resident of ${purok}, Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS CERTIFIES FURTHER that the above-named individual is known to me to be of good moral character and a law-abiding citizen with no derogatory record or pending criminal/civil case filed against him/her before the Lupong Tagapamayapa of this Barangay.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'This clearance is being issued upon the request of the interested party for the purpose of:',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 30, 'lineHeight' => 1.15]
        );
        $section->addText(
            '${purpose}.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued this ${date} at Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 180, 'lineHeight' => 1.15]
        );

        $this->addOfficialSignaturesAndFooter($section);

        $this->saveTo($phpWord, 'clearance_template.docx');
    }

    // ---------------------------------------------------------------
    // 2. Senior Citizen Certification
    // ---------------------------------------------------------------

    private function generateSeniorCitizenCertificationTemplate(): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $this->createOfficialSection($phpWord);

        $section->addText(
            'SENIOR CITIZEN CERTIFICATION',
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );

        $section->addTextBreak(1);

        $section->addText(
            'TO WHOM IT MAY CONCERN:',
            ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
            ['alignment' => Jc::START, 'spaceAfter' => 120]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS IS TO CERTIFY that ${name}, born on ${birthdate}, is currently ${age} years old and a registered senior citizen resident of ${purok}, Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS CERTIFIES FURTHER that he/she is active in local community activities and is recognized under the Barangay Senior Citizen Roster.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued upon request for Senior Citizen Identification / OSCA Registration / ${purpose}.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued this ${date} at Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 180, 'lineHeight' => 1.15]
        );

        $this->addOfficialSignaturesAndFooter($section);

        $this->saveTo($phpWord, 'senior_citizen_certification_template.docx');
    }

    // ---------------------------------------------------------------
    // 3. Certification for Senior Citizen Cash Assistance
    // ---------------------------------------------------------------

    private function generateSeniorCitizenCashAssistanceTemplate(): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $this->createOfficialSection($phpWord);

        $section->addText(
            'CERTIFICATION FOR SENIOR CITIZEN CASH ASSISTANCE',
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );

        $section->addTextBreak(1);

        $section->addText(
            'TO WHOM IT MAY CONCERN:',
            ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
            ['alignment' => Jc::START, 'spaceAfter' => 120]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS IS TO CERTIFY that ${name}, ${age} years old, is a living, bonafide senior citizen resident of ${purok}, Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS CERTIFIES FURTHER that the bearer is physically verified to be alive and qualified to claim the Senior Citizen Cash Subsidy / Financial Grant.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued this ${date} at Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 180, 'lineHeight' => 1.15]
        );

        $this->addOfficialSignaturesAndFooter($section);

        $this->saveTo($phpWord, 'senior_citizen_cash_assistance_template.docx');
    }

    // ---------------------------------------------------------------
    // 4. Certification for Burial Assistance
    // ---------------------------------------------------------------

    private function generateBurialAssistanceTemplate(): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $this->createOfficialSection($phpWord);

        $section->addText(
            'CERTIFICATION FOR BURIAL ASSISTANCE',
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );

        $section->addTextBreak(1);

        $section->addText(
            'TO WHOM IT MAY CONCERN:',
            ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
            ['alignment' => Jc::START, 'spaceAfter' => 120]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS IS TO CERTIFY that the late ${deceased_name}, who passed away on ${date_of_death}, was a registered senior citizen resident of ${purok}, Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS CERTIFIES FURTHER that ${name}, ${relationship}, is the authorized family member handling the funeral arrangements and claiming the burial benefit.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued upon request for Senior Citizen Burial Financial Assistance.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued this ${date} at Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 180, 'lineHeight' => 1.15]
        );

        $this->addOfficialSignaturesAndFooter($section);

        $this->saveTo($phpWord, 'burial_assistance_template.docx');
    }

    // ---------------------------------------------------------------
    // 5. Barangay Business Clearance
    // ---------------------------------------------------------------

    private function generateBusinessClearanceTemplate(): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $this->createOfficialSection($phpWord);

        $section->addText(
            'BARANGAY BUSINESS CLEARANCE',
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );

        $section->addTextBreak(1);

        $section->addText(
            'TO WHOM IT MAY CONCERN:',
            ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
            ['alignment' => Jc::START, 'spaceAfter' => 120]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS IS TO CERTIFY that the business or trade activity registered under ${business_name}, located at ${business_address}, Barangay Cogon East, Carmen, Cebu, and operated/owned by ${name}, has been inspected and found to be in conformity with the provisions of existing Barangay Ordinances, rules, and regulations enforced in this Barangay.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'This clearance is hereby granted for the purpose of securing/renewing a Business Permit and/or ${purpose}.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued this ${date} at Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 180, 'lineHeight' => 1.15]
        );

        $this->addOfficialSignaturesAndFooter($section);

        $this->saveTo($phpWord, 'business_clearance_template.docx');
    }

    // ---------------------------------------------------------------
    // 6. Certificate of Residency (Official Template)
    // ---------------------------------------------------------------

    private function generateResidencyTemplate(): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $this->createOfficialSection($phpWord);

        $section->addText(
            'CERTIFICATE OF RESIDENCY',
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );

        $section->addTextBreak(1);

        $section->addText(
            'TO WHOM IT MAY CONCERN:',
            ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
            ['alignment' => Jc::START, 'spaceAfter' => 120]
        );

        $section->addTextBreak(1);

        $section->addText(
            'This is to certify that ${name}, ${age} years of age, is a permanent and bona fide resident of Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Based on the records of this office, the aforementioned individual has been residing in this barangay and is recognized as a legitimate member of our community.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'This certification is being issued upon the request of the above-named person for the purpose of: ${purpose}.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued this ${date} at the Office of the Punong Barangay, Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 180, 'lineHeight' => 1.15]
        );

        $this->addSingleCaptainFooter($section);

        $this->saveTo($phpWord, 'residency_template.docx');
    }

    // ---------------------------------------------------------------
    // 7. Business Permit template (Standard fallback)
    // ---------------------------------------------------------------

    private function generateBusinessPermitTemplate(): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        $section = $this->headerSection($phpWord);

        $section->addText(
            'BARANGAY BUSINESS CLEARANCE',
            ['name' => 'Times New Roman', 'size' => 16, 'bold' => true, 'underline' => Font::UNDERLINE_SINGLE],
            ['alignment' => Jc::CENTER]
        );

        $section->addTextBreak(2);

        $section->addText('TO WHOM IT MAY CONCERN:', ['bold' => true]);

        $section->addTextBreak(1);

        $body = $section->addTextRun(['alignment' => Jc::BOTH]);
        $body->addText("\t\tThis is to certify that ");
        $body->addText('${name}', ['bold' => true]);
        $body->addText(' has complied with the requirements to operate a business enterprise in this Barangay for ');
        $body->addText('${purpose}', ['bold' => true]);
        $body->addText('.');

        $section->addTextBreak(1);

        $section->addText(
            'Issued this ${date} at the Barangay Hall.',
            ['name' => 'Times New Roman', 'size' => 12],
            ['alignment' => Jc::BOTH]
        );

        $section->addTextBreak(1);
        $section->addText(
            'Tracking No: ${tracking_number}',
            ['name' => 'Times New Roman', 'size' => 10, 'italic' => true]
        );

        $this->addSignatureBlock($section);
        $this->addOfficeCopy($section);

        $this->saveTo($phpWord, 'business_permit_template.docx');
    }

    // ---------------------------------------------------------------
    // 8. Certificate of Indigency (Official Template)
    // ---------------------------------------------------------------

    private function generateIndigencyTemplate(): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $this->createOfficialSection($phpWord);

        $section->addText(
            'CERTIFICATE OF INDIGENCY',
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );

        $section->addTextBreak(1);

        $section->addText(
            'TO WHOM IT MAY CONCERN:',
            ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
            ['alignment' => Jc::START, 'spaceAfter' => 120]
        );

        $section->addTextBreak(1);

        $section->addText(
            'This is to certify that ${name}, ${age} years of age, is a bona fide resident of Barangay Cogon East, Carmen, Cebu, and is known to me to be of good moral character.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'This further certifies that the above-named person belongs to an indigent family in this barangay whose combined family income is not sufficient to meet their basic everyday needs.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'This certification is being issued upon the request of the interested party for the purpose of: ${purpose}.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued this ${date} at the Office of the Punong Barangay, Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 180, 'lineHeight' => 1.15]
        );

        $this->addSingleCaptainFooter($section);

        $this->saveTo($phpWord, 'indigency_template.docx');
    }

    // ---------------------------------------------------------------
    // 5. Barangay Certification for Electric Connection
    // ---------------------------------------------------------------

    private function generateElectricConnectionTemplate(): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $this->createOfficialSection($phpWord);

        $section->addText(
            'BARANGAY CERTIFICATION FOR ELECTRIC CONNECTION',
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );

        $section->addTextBreak(1);

        $section->addText(
            'TO WHOM IT MAY CONCERN:',
            ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
            ['alignment' => Jc::START, 'spaceAfter' => 120]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS IS TO CERTIFY that ${name}, of legal age, ${civil_status}, is a bonafide resident of ${purok}, Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS IS TO FURTHER CERTIFY that this Barangay interposes NO OBJECTION to the application of the above-named resident for an electric meter connection (CEBECO II) at his/her residential premise located within our barangay jurisdiction.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued upon the request of the interested party for Electric Meter Connection purposes.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued this ${date} at Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 180, 'lineHeight' => 1.15]
        );

        $this->addOfficialSignaturesAndFooter($section);

        $this->saveTo($phpWord, 'electric_connection_template.docx');
        @copy(storage_path('app/electric_connection_template.docx'), storage_path('app/electric_certificate_template.docx'));
    }

    // ---------------------------------------------------------------
    // 6. Acknowledgement Receipt
    // ---------------------------------------------------------------

    private function generateAcknowledgementReceiptTemplate(): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $this->createOfficialSection($phpWord);

        $section->addText(
            'ACKNOWLEDGEMENT RECEIPT',
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );

        $section->addTextBreak(1);

        $section->addText(
            'TO WHOM IT MAY CONCERN:',
            ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
            ['alignment' => Jc::START, 'spaceAfter' => 120]
        );

        $section->addTextBreak(1);

        $section->addText(
            'RECEIVED from ${name}, resident of ${purok}, Barangay Cogon East, Carmen, Cebu, the total amount of ${amount_in_words} (₱${amount}) as payment for ${purpose}.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 160, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Received by: ${treasurer_name}',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::START, 'spaceAfter' => 40]
        );
        $section->addText(
            'Designation: Barangay Treasurer',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::START, 'spaceAfter' => 40]
        );
        $section->addText(
            'Date: ${date}',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::START, 'spaceAfter' => 160]
        );

        $this->addOfficialSignaturesAndFooter($section);

        $this->saveTo($phpWord, 'acknowledgement_receipt_template.docx');
    }

    // ---------------------------------------------------------------
    // 7. Certificate of Cohabitation
    // ---------------------------------------------------------------

    private function generateCohabitationTemplate(): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $this->createOfficialSection($phpWord);

        $section->addText(
            'CERTIFICATE OF COHABITATION',
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );

        $section->addTextBreak(1);

        $section->addText(
            'TO WHOM IT MAY CONCERN:',
            ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
            ['alignment' => Jc::START, 'spaceAfter' => 120]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS IS TO CERTIFY that ${name} and ${partner_name}, both of legal age, are bonafide residents of ${purok}, Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS IS TO CERTIFY FURTHER that both parties have been living together as common-law husband and wife under one roof without the benefit of formal marriage for ${years_cohabitating} consecutive years.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued upon request for ${purpose}.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued this ${date} at Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 180, 'lineHeight' => 1.15]
        );

        $this->addOfficialSignaturesAndFooter($section);

        $this->saveTo($phpWord, 'cohabitation_certificate_template.docx');
        @copy(storage_path('app/cohabitation_certificate_template.docx'), storage_path('app/cohabitation_template.docx'));
    }

    // ---------------------------------------------------------------
    // 8. Barangay Certificate
    // ---------------------------------------------------------------

    private function generateBarangayCertificateTemplate(): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $this->createOfficialSection($phpWord);

        $section->addText(
            'BARANGAY CERTIFICATE',
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );

        $section->addTextBreak(1);

        $section->addText(
            'TO WHOM IT MAY CONCERN:',
            ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
            ['alignment' => Jc::START, 'spaceAfter' => 120]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS IS TO CERTIFY that ${name}, ${age} years old, ${civil_status}, is a law-abiding citizen residing at ${purok}, Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS CERTIFIES FURTHER that he/she is known to be of good moral character with no adverse record on file in this office.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued upon request for ${purpose}.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued this ${date} at Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 180, 'lineHeight' => 1.15]
        );

        $this->addOfficialSignaturesAndFooter($section);

        $this->saveTo($phpWord, 'barangay_certificate_template.docx');
    }

    // ---------------------------------------------------------------
    // 9. Barangay Certification (R.A. 11261 - First Time Jobseeker)
    // ---------------------------------------------------------------

    private function generateFirstTimeJobseekerTemplate(): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $this->createOfficialSection($phpWord);

        $section->addText(
            'BARANGAY CERTIFICATION (R.A. 11261 - FIRST TIME JOBSEEKER)',
            ['name' => 'Times New Roman', 'size' => 13.5, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );

        $section->addTextBreak(1);

        $section->addText(
            'TO WHOM IT MAY CONCERN:',
            ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
            ['alignment' => Jc::START, 'spaceAfter' => 120]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS IS TO CERTIFY that ${name}, a resident of ${purok}, Barangay Cogon East, Carmen, Cebu for ${residence_years} years, is a qualified availee of Republic Act No. 11261, otherwise known as the First Time Jobseekers Assistance Act.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'I further certify that the bearer was informed of his/her rights and obligations through the Oath of Undertaking signed in the presence of Barangay Officials.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Valid for one (1) year from the date of issuance.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued this ${date} at Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 180, 'lineHeight' => 1.15]
        );

        $this->addOfficialSignaturesAndFooter($section);

        $this->saveTo($phpWord, 'first_time_jobseeker_template.docx');
        @copy(storage_path('app/first_time_jobseeker_template.docx'), storage_path('app/first_time_job_seeker_template.docx'));
        @copy(storage_path('app/first_time_jobseeker_template.docx'), storage_path('app/job_seeker_template.docx'));
    }

    // ---------------------------------------------------------------
    // 10. Barangay Certification (For Probation & Bail)
    // ---------------------------------------------------------------

    private function generateProbationBailTemplate(): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $this->createOfficialSection($phpWord);

        $section->addText(
            'BARANGAY CERTIFICATION (FOR PROBATION & BAIL)',
            ['name' => 'Times New Roman', 'size' => 13.5, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );

        $section->addTextBreak(1);

        $section->addText(
            'TO WHOM IT MAY CONCERN:',
            ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
            ['alignment' => Jc::START, 'spaceAfter' => 120]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS IS TO CERTIFY that ${name}, ${age} years old, resident of ${purok}, Barangay Cogon East, Carmen, Cebu, has no derogatory record reported in the Barangay Blotter.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'THIS CERTIFIES FURTHER that the bearer is a peaceful, cooperative resident in the neighborhood, and the Barangay has no objection to his/her application for court probation supervision or bail approval.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued upon request for ${purpose}.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Issued this ${date} at Barangay Cogon East, Carmen, Cebu.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 180, 'lineHeight' => 1.15]
        );

        $this->addOfficialSignaturesAndFooter($section);

        $this->saveTo($phpWord, 'probation_bail_template.docx');
        @copy(storage_path('app/probation_bail_template.docx'), storage_path('app/bail_certificate_template.docx'));
    }

    // ---------------------------------------------------------------
    // 11. Oath of Undertaking (R.A. 11261)
    // ---------------------------------------------------------------

    private function generateOathOfUndertakingTemplate(): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $this->createOfficialSection($phpWord);

        $section->addText(
            'OATH OF UNDERTAKING (R.A. 11261)',
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );

        $section->addTextBreak(1);

        $section->addText(
            'I, ${name}, ${age} years of age, resident of ${purok}, Barangay Cogon East, Carmen, Cebu, availing the benefits of Republic Act 11261 (First Time Jobseekers Act), do hereby declare and undertake:',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addText(
            '1. That this is the first time I am actively seeking employment.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 60, 'lineHeight' => 1.15]
        );
        $section->addText(
            '2. That I am aware the benefits under this Act are valid for one (1) year from issuance and can only be availed once.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 60, 'lineHeight' => 1.15]
        );
        $section->addText(
            '3. That I will report back to the Barangay personally or via text once employed.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 60, 'lineHeight' => 1.15]
        );
        $section->addText(
            '4. That I consent to the inclusion of my personal information in the First-Time Jobseeker Roster in compliance with the Data Privacy Act.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.15]
        );

        $section->addTextBreak(1);

        $section->addText(
            'Declarant Signature: ___________________________',
            ['name' => 'Times New Roman', 'size' => 11],
            ['spaceAfter' => 60]
        );
        $section->addText(
            'Witnessed by: ___________________________ (Barangay Official)',
            ['name' => 'Times New Roman', 'size' => 11],
            ['spaceAfter' => 120]
        );

        $this->addOfficialSignaturesAndFooter($section);

        $this->saveTo($phpWord, 'oath_of_undertaking_template.docx');
    }
}
