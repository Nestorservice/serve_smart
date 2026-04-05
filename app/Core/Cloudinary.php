<?php
/**
 * SIGR - Cloudinary Integration
 * 
 * A simple wrapper to upload images to Cloudinary without external SDKs.
 */

namespace Core;

class Cloudinary
{
    private string $cloudName;
    private string $apiKey;
    private string $apiSecret;

    public function __construct()
    {
        $this->cloudName = getenv('CLOUDINARY_CLOUD_NAME') ?: '';
        $this->apiKey = getenv('CLOUDINARY_API_KEY') ?: '';
        $this->apiSecret = getenv('CLOUDINARY_API_SECRET') ?: '';
    }

    /**
     * Check if Cloudinary is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->cloudName) && !empty($this->apiKey) && !empty($this->apiSecret);
    }

    /**
     * Upload an image to Cloudinary
     * 
     * @param string $filePath Local path to the file
     * @param string $folder Optional folder name
     * @return string|null The secure URL of the uploaded image
     */
    public function upload(string $filePath, string $folder = 'serve_smart'): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $timestamp = time();
        $params = [
            'folder' => $folder,
            'timestamp' => $timestamp
        ];

        // Sort params alphabetically (required for signature)
        ksort($params);
        
        $paramString = "";
        foreach ($params as $key => $value) {
            $paramString .= "$key=$value&";
        }
        $paramString = rtrim($paramString, '&');
        
        // Generate signature
        $signature = sha1($paramString . $this->apiSecret);

        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";

        $postData = array_merge($params, [
            'file' => new \CURLFile($filePath),
            'api_key' => $this->apiKey,
            'signature' => $signature
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Cloudinary Upload Error: " . $error);
            return null;
        }

        $result = json_decode($response, true);
        
        if (isset($result['secure_url'])) {
            return $result['secure_url'];
        }

        if (isset($result['error'])) {
            error_log("Cloudinary API Error: " . $result['error']['message']);
        }

        return null;
    }
}
