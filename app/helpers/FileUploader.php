<?php

class FileUploader {
    static function downloadFile($leadId, $dirName, $photos) {
        $photos = end($photos);

        $fileId = $photos['file_id'];

        $photoInfo = file_get_contents("https://api.telegram.org/bot" . $_ENV['BOT_TOKEN'] . "/getFile?file_id=" . $fileId);

        $photoInfo = json_decode($photoInfo, true);

        $filePath = $photoInfo['result']['file_path'];

        $fileName = time() . "_$leadId.jpg" ;

        $uploadedFilePath = UPLOADS_DIR . "/$dirName/$fileName";

        file_put_contents(
            $uploadedFilePath,
            file_get_contents("https://api.telegram.org/file/bot" . $_ENV['BOT_TOKEN'] . "/$filePath")
        );

        return $fileName;
    }
}
