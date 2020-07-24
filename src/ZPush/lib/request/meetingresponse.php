<?php
/***********************************************
* File      :   meetingresponse.php
* Project   :   Z-Push
* Descr     :   Provides the MEETINGRESPONSE command
*
* Created   :   16.02.2012
*
* Copyright 2007 - 2016 Zarafa Deutschland GmbH
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License, version 3,
* as published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* Consult LICENSE file for details
************************************************/

class MeetingResponse extends RequestProcessor
{

    /**
     * Handles the MeetingResponse command
     *
     * @param int       $commandCode
     *
     * @access public
     * @return boolean
     */
    public function Handle($commandCode)
    {
        $requests = [];

        if (!self::$decoder->getElementStartTag(SYNC_MEETINGRESPONSE_MEETINGRESPONSE)) {
            return false;
        }

        while (self::$decoder->getElementStartTag(SYNC_MEETINGRESPONSE_REQUEST)) {
            $req = [];
            WBXMLDecoder::ResetInWhile("meetingResponseRequest");
            while (WBXMLDecoder::InWhile("meetingResponseRequest")) {
                if (self::$decoder->getElementStartTag(SYNC_MEETINGRESPONSE_USERRESPONSE)) {
                    $req["response"] = self::$decoder->getElementContent();
                    if (!self::$decoder->getElementEndTag()) {
                        return false;
                    }
                }

                if (self::$decoder->getElementStartTag(SYNC_MEETINGRESPONSE_FOLDERID)) {
                    $req["folderid"] = self::$decoder->getElementContent();
                    if (!self::$decoder->getElementEndTag()) {
                        return false;
                    }
                }

                if (self::$decoder->getElementStartTag(SYNC_MEETINGRESPONSE_REQUESTID)) {
                    $req["requestid"] = self::$decoder->getElementContent();
                    if (!self::$decoder->getElementEndTag()) {
                        return false;
                    }
                }

                $e = self::$decoder->peek();
                if ($e[EN_TYPE] == EN_TYPE_ENDTAG) {
                    self::$decoder->getElementEndTag();
                    break;
                }
            }
            array_push($requests, $req);
        }

        if (!self::$decoder->getElementEndTag()) {
            return false;
        }

        // output the error code, plus the ID of the calendar item that was generated by the
        // accept of the meeting response
        self::$encoder->StartWBXML();
        self::$encoder->startTag(SYNC_MEETINGRESPONSE_MEETINGRESPONSE);

        foreach ($requests as $req) {
            $status = SYNC_MEETRESPSTATUS_SUCCESS;

            try {
                $backendFolderId = self::$deviceManager->GetBackendIdForFolderId($req["folderid"]);

                // if the source folder is an additional folder the backend has to be setup correctly
                if (!self::$backend->Setup(ZPush::GetAdditionalSyncFolderStore($backendFolderId))) {
                    throw new StatusException(sprintf("HandleMoveItems() could not Setup() the backend for folder id %s/%s", $req["folderid"], $backendFolderId), SYNC_MEETRESPSTATUS_SERVERERROR);
                }

                $calendarid = self::$backend->MeetingResponse($req["requestid"], $backendFolderId, $req["response"]);
                if ($calendarid === false) {
                    throw new StatusException("HandleMeetingResponse() not possible", SYNC_MEETRESPSTATUS_SERVERERROR);
                }
            } catch (StatusException $stex) {
                $status = $stex->getCode();
            }

            self::$encoder->startTag(SYNC_MEETINGRESPONSE_RESULT);
                self::$encoder->startTag(SYNC_MEETINGRESPONSE_REQUESTID);
                    self::$encoder->content($req["requestid"]);
                self::$encoder->endTag();

                self::$encoder->startTag(SYNC_MEETINGRESPONSE_STATUS);
                    self::$encoder->content($status);
                self::$encoder->endTag();

            if ($status == SYNC_MEETRESPSTATUS_SUCCESS && !empty($calendarid)) {
                self::$encoder->startTag(SYNC_MEETINGRESPONSE_CALENDARID);
                    self::$encoder->content($calendarid);
                self::$encoder->endTag();
            }
            self::$encoder->endTag();
            self::$topCollector->AnnounceInformation(sprintf("Operation status %d", $status), true);
        }
        self::$encoder->endTag();

        return true;
    }
}
