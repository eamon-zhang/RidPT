<?php

namespace Rid\Http;

use Rid\Helpers\IoHelper;

/**
 * Error类
 */
class Error
{
    // 异常处理
    public function handleException(\Throwable $e)
    {
        $errors     = [
            'code'    => $e->getCode(),
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'type'    => get_class($e),
            'trace'   => $e->getTraceAsString(),
        ];

        if (container()->get('response')->getResponderStatus() !== false) {  // 在Web环境，存在 \Swoole\Http\Response 对象
            // debug处理 & exit处理
            if ($e instanceof \Rid\Exceptions\DebugException) {
                container()->get('response')->setContent($e->getMessage());
            } else {
                // 错误参数定义
                $statusCode = $e instanceof \Rid\Exceptions\NotFoundException ? 404 : 500;
                $errors['status'] = $statusCode;
                // 日志处理
                if (!($e instanceof \Rid\Exceptions\NotFoundException)) {
                    $message = "{$errors['message']}" . PHP_EOL;
                    $message .= "[type] {$errors['type']} [code] {$errors['code']}" . PHP_EOL;
                    $message .= "[file] {$errors['file']} [line] {$errors['line']}" . PHP_EOL;
                    $message .= "[trace] {$errors['trace']}" . PHP_EOL;
                    $message .= '$_SERVER' . substr(print_r(container()->get('request')->server->all() + container()->get('request')->headers->all(), true), 5);
                    $message .= '$_GET' . substr(print_r(container()->get('request')->query->all(), true), 5);
                    $message .= '$_POST' . substr(print_r(container()->get('request')->request->all(), true), 5, -1);
                    $message .= PHP_EOL . 'Memory used: ' . memory_get_usage();
                    IoHelper::getIo()->error($message);
                    container()->get('logger')->error($message);
                }
                // 清空系统错误
                ob_get_contents() and ob_clean();

                container()->get('response')->setStatusCode($statusCode);
                container()->get('response')->setContent(container()->get('view')->render('error', $errors));
            }

            container()->get('response')->prepare(container()->get('request'));
            container()->get('response')->send();
        } else {  // 在Task或Timer环境 （使用 Console\Error的处理方法）
            if ($e instanceof \Rid\Exceptions\DebugException) {
                $content = $e->getMessage();
                IoHelper::getIo()->note($content);
            }

            // 格式化输出
            $message = $errors['message'] . PHP_EOL;
            $message .= "{$errors['type']} code {$errors['code']}" . PHP_EOL;
            $message .= $errors['file'] . ' line ' . $errors['line'] . PHP_EOL;
            $message .= str_replace("\n", PHP_EOL, $errors['trace']) . PHP_EOL;

            // 日志处理
            if (!($e instanceof \Rid\Exceptions\NotFoundException)) {
                $log_message = $message . '$_SERVER' . substr(print_r($_SERVER, true), 5, -1);
                container()->get('logger')->error($log_message);
            }
            // 清空系统错误
            ob_get_contents() and ob_clean();

            // 写入stdout
            IoHelper::getIo()->error($message);
        }
    }
}
