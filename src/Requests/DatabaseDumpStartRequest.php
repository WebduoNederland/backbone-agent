<?php

namespace WebduoNederland\BackboneAgent\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int $id
 * @property string $file_name
 * @property array $exclude_tables
 * @property array $sftp_credentials
 */
class DatabaseDumpStartRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|numeric',
            'file_name' => 'required',
            'exclude_tables' => 'present|array',
            'sftp_credentials' => 'required|array',
            'sftp_credentials.host' => 'required|string',
            'sftp_credentials.username' => 'required|string',
            'sftp_credentials.password' => 'required|string',
        ];
    }
}
