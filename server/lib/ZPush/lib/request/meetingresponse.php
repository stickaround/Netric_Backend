<?php
/***********************************************
* File      :   meetingresponse.php
* Project   :   Z-Push
* Descr     :   Provides the MEETINGRESPONSE command
*
* Created   :   16.02.2012
*
* Copyright 2007 - 2013 Zarafa Deutschland GmbH
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License, version 3,
* as published by the Free Software Foundation with the following additional
* term according to sec. 7:
*
* According to sec. 7 of the GNU Affero General Public License, version 3,
* the terms of the AGPL are supplemented with the following terms:
*
* "Zarafa" is a registered trademark of Zarafa B.V.
* "Z-Push" is a registered trademark of Zarafa Deutschland GmbH
* The licensing of the Program under the AGPL does not imply a trademark license.
* Therefore any rights, title and interest in our trademarks remain entirely with us.
*
* However, if you propagate an unmodified version of the Program you are
* allowed to use the term "Z-Push" to indicate that you distribute the Program.
* Furthermore you may use our trademarks where it is necessary to indicate
* the intended purpose of a product or service provided you use it in accordance
* with honest practices in industrial or commercial matters.
* If you want to propagate modified versions of the Program under the name "Z-Push",
* you may only do so if you have a written permission by Zarafa Deutschland GmbH
* (to acquire a permission please contact Zarafa at trademark@zarafa.com).
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

class MeetingResponse extends RequestProcessor {

    /**
     * Handles the MeetingResponse command
     *
     * @param int       $commandCode
     *
     * @access public
     * @return boolean
     */
    public function Handle($commandCode) {
        $requests = Array();

        if(!self::$decoder->getElementStartTag(SYNC_MEETINGRESPONSE_MEETINGRESPONSE))
            return false;

        while(self::$decoder->getElementStartTag(SYNC_MEETINGRESPONSE_REQUEST)) {
            $req = Array();
            while(1) {
                if(self::$decoder->getElementStartTag(SYNC_MEETINGRESPONSE_USERRESPONSE)) {
                    $req["response"] = self::$decoder->getElementContent();
                    if(!self::$decoder->getElementEndTag())
                        return false;
                }

                if(self::$decoder->getElementStartTag(SYNC_MEETINGRESPONSE_FOLDERID)) {
                    $req["folderid"] = self::$decoder->getElementContent();
                    if(!self::$decoder->getElementEndTag())
                        return false;
                }

                if(self::$decoder->getElementStartTag(SYNC_MEETINGRESPONSE_REQUESTID)) {
                    $req["requestid"] = self::$decoder->getElementContent();
                    if(!self::$decoder->getElementEndTag())
                        return false;
                }

                $e = self::$decoder->peek();
                if($e[EN_TYPE] == EN_TYPE_ENDTAG) {
                    self::$decoder->getElementEndTag();
                    break;
                }
            }
            array_push($requests, $req);
        }

        if(!self::$decoder->getElementEndTag())
            return false;

        // output the error code, plus the ID of the calendar item that was generated by the
        // accept of the meeting response
        self::$encoder->StartWBXML();
        self::$encoder->startTag(SYNC_MEETINGRESPONSE_MEETINGRESPONSE);

        foreach($requests as $req) {
            $status = SYNC_MEETRESPSTATUS_SUCCESS;

            try {
                $calendarid = self::$backend->MeetingResponse($req["requestid"], $req["folderid"], $req["response"]);
                if ($calendarid === false)
                    throw new StatusException("HandleMeetingResponse() not possible", SYNC_MEETRESPSTATUS_SERVERERROR);
            }
            catch (StatusException $stex) {
                $status = $stex->getCode();
            }

            self::$encoder->startTag(SYNC_MEETINGRESPONSE_RESULT);
                self::$encoder->startTag(SYNC_MEETINGRESPONSE_REQUESTID);
                    self::$encoder->content($req["requestid"]);
                self::$encoder->endTag();

                self::$encoder->startTag(SYNC_MEETINGRESPONSE_STATUS);
                    self::$encoder->content($status);
                self::$encoder->endTag();

                if($status == SYNC_MEETRESPSTATUS_SUCCESS && !empty($calendarid)) {
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
?>