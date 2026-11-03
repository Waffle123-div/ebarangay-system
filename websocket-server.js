import http from 'http';
import { WebSocketServer, WebSocket } from 'ws';

const PORT = process.env.WS_PORT || 8080;
const server = http.createServer((req, res) => {
    // Enable CORS
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    // Health check endpoint
    if (req.method === 'GET' && req.url === '/health') {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ status: 'ok', clients: wss.clients.size, time: new Date().toISOString() }));
        return;
    }

    // Publish endpoint for PHP / Laravel to broadcast events
    if (req.method === 'POST' && req.url === '/publish') {
        let body = '';
        req.on('data', chunk => { body += chunk; });
        req.on('end', () => {
            try {
                const payload = JSON.parse(body || '{}');
                const message = JSON.stringify(payload);
                let sentCount = 0;
                wss.clients.forEach(client => {
                    if (client.readyState === WebSocket.OPEN) {
                        client.send(message);
                        sentCount++;
                    }
                });
                console.log(`[WebSocket] Broadcasted event "${payload.event || 'custom'}" to ${sentCount} clients`);
                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ success: true, deliveredTo: sentCount }));
            } catch (err) {
                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: err.message }));
            }
        });
        return;
    }

    res.writeHead(404);
    res.end();
});

const wss = new WebSocketServer({ server });

wss.on('connection', (ws, req) => {
    const ip = req.socket.remoteAddress;
    console.log(`[WebSocket] Client connected from ${ip} (Total clients: ${wss.clients.size})`);
    
    // Send welcome / ping handshake
    ws.send(JSON.stringify({ event: 'connected', message: 'Connected to e-Barangay Real-time Service' }));

    ws.on('message', message => {
        try {
            const data = JSON.parse(message);
            if (data.type === 'ping') {
                ws.send(JSON.stringify({ type: 'pong' }));
            }
        } catch(e) {}
    });

    ws.on('close', () => {
        console.log(`[WebSocket] Client disconnected (Remaining: ${wss.clients.size})`);
    });
});

server.listen(PORT, '0.0.0.0', () => {
    console.log(`⚡ e-Barangay WebSocket Server running on ws://0.0.0.0:${PORT}`);
    console.log(`📡 Broadcast API available at http://127.0.0.1:${PORT}/publish`);
});

