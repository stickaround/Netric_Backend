<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Mail;

class Storage
{
    // maildir and IMAP flags, using IMAP names, where possible to be able to distinguish between IMAP
    // system flags and other flags
    const FLAG_PASSED   = 'Passed';
    const FLAG_SEEN     = '\Seen';
    const FLAG_UNSEEN   = '\Unseen';
    const FLAG_ANSWERED = '\Answered';
    const FLAG_FLAGGED  = '\Flagged';
    const FLAG_DELETED  = '\Deleted';
    const FLAG_DRAFT    = '\Draft';
    const FLAG_RECENT   = '\Recent';
}
