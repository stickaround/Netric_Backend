<?php

declare(strict_types=1);

namespace Netric\Mail\Maildrop;

use Netric\Entity\EntityInterface;
use Netric\Entity\ObjType\UserEntity;
use Netric\FileSystem\FileSystem;
use PhpMimeMailParser\Attachment as MailParserAttachment;

/**
 * Some commone parsing functions that all maildrop drivers will probably need
 */
abstract class AbstractMaildrop
{
    /**
     * Process attachments for a message being parsed by mimeparse
     *
     * @param MailParserAttachment $parserAttach The attachment to import
     * @param EmailMessageEntity $email The email we are adding attachments to
     * @return bool true on success, false on failure
     */
    protected function importAttachments(
        MailParserAttachment $parserAttach,
        EntityInterface $entity,
        UserEntity $user,
        FileSystem $fileSystem
    ) {
        /*
         * Write attachment to temp file
         *
         * It is important to use streams here to try and keep the attachment out of
         * memory if possible. The parser should already have decoded the bodies for
         * us so no need to use base64_decode or any other decoding.
         */
        $tmpFile = tmpfile();
        $buf = null;
        while (($buf = $parserAttach->read()) != false) {
            fwrite($tmpFile, $buf);
        }

        // Rewind stream
        fseek($tmpFile, 0);

        // Stream the temp file into the fileSystem
        $file = $this->fileSystem->createFile(
            "%tmp%",
            $parserAttach->getFilename(),
            $user,
            true
        );

        if ($this->fileSystem->writeFile($file, $tmpFile, $user)) {
            $entity->addMultiValue("attachments", $file->getEntityId(), $file->getName());
        }

        // Cleanup
        $tmpFile = null;
    }
}
