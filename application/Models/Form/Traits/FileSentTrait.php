<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 10:07 PM
 */

namespace App\Models\Form\Traits;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

trait FileSentTrait
{
    use actionRateLimitCheckTrait;

    protected function getSendFileName(): string
    {
        return static::$SEND_FILE_NAME ?? 'file';
    }

    protected function getSendFileContentLength(): int
    {
        return static::$SEND_FILE_CONTENT_LENGTH ?? 0;
    }

    protected function getSendFileContentType(): string
    {
        return static::$SEND_FILE_CONTENT_TYPE ?? 'application/octet-stream';
    }

    protected function getSendFileCacheControlStatus(): bool
    {
        return static::$SEND_FILE_CACHE_CONTROL ?? false;
    }

    final private function setRespHeaders()
    {
        if ('application/octet-stream' !== $fileContentType = $this->getSendFileContentType()) {
            container()->get('response')->headers->set('Content-Type', $fileContentType);
        }

        if (0 !== $fileSize = $this->getSendFileContentLength()) {
            container()->get('response')->headers->set('Content-Length', $fileSize);
        }

        if ($this->getSendFileCacheControlStatus()) {
            container()->get('response')->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            container()->get('response')->headers->set('Pragma', 'no-cache');
            container()->get('response')->headers->set('Expires', '0');
        }

        container()->get('response')->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $this->getSendFileName()
        );
    }

    abstract protected function getSendFileContent();

    protected function hookFileContentSend()
    {
    }

    final public function sendFileContentToClient()
    {
        $this->hookFileContentSend();
        $this->setRespHeaders();
        return $this->getSendFileContent();
    }
}
