<?php

declare(strict_types=1);

return [
    /*
    | URL base da API de processamento (FastAPI). O Laravel orquestra; o Python processa.
    */
    'base_url' => env('PYTHON_API_BASE_URL', 'http://127.0.0.1:8765'),

    /*
    | URL base do WebSocket da API de processamento (consumido pelo browser para
    | mostrar progresso em tempo real). Ex: ws://127.0.0.1:8765
    */
    'ws_url' => env('PYTHON_API_WS_URL', 'ws://127.0.0.1:8765'),

    /*
    | Token enviado no header Authorization das chamadas Laravel -> Python.
    | Deve casar com PYTHON_API_TOKEN no .env do projeto Python.
    | O Laravel orquestra; o Python processa.
    */
    'token' => env('PYTHON_API_TOKEN', ''),

    /*
    | Timeout (segundos) das chamadas HTTP síncronas.
    */
    'timeout' => (int) env('PYTHON_API_TIMEOUT', 120),

    /*
    | Token que o Python deve enviar de volta no webhook de callback.
    | Validado em POST /api/video-processor/callbacks.
    */
    'callback_token' => env('PYTHON_CALLBACK_TOKEN', ''),

    /*
    | URL pública do callback que o Laravel envia ao processador.
    */
    'callback_url' => env('APP_URL', 'http://localhost:8000').'/api/video-processor/callbacks',

    /*
    | Disco (filesystem) usado para o MinIO, compartilhado com o Python.
    */
    'storage_disk' => 'minio',
    'storage_bucket' => env('MINIO_BUCKET', 'auto-post'),
];
