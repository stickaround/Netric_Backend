<?php
namespace Netric\EntityDefinition;

/**
 * Constants for system object types
 */
class ObjectTypes
{
    /**
     * Constants for system objects types so we don't use strings everywhere
     * and so we can change them in the future if we want to
     */
    const INVOICE = 'invoice';
    const CONTACT = 'customer';
    const CONTENT_FEED = 'content_feed';
    const CONTENT_FEED_POST = 'content_feed_post';
    const DISCUSSION = 'discussion';
    const EMAIL_ACCOUNT = 'email_account';
    const EMAIL_THREAD = 'email_thread';
    const EMAIL_MESSAGE = 'email_message';
    const EMAIL_MESSAGE_ATTACHMENT = 'email_message_attachment';
    const EMAIL_CAMPAIGN = 'email_campaign';
    const PROJECT_MILESTONE = 'project_milestone';
    const CALENDAR_EVENT = 'calendar_event';
    const CALENDAR_EVENT_PROPOSAL = 'calendar_event_proposal';
    const REPORT = 'report';
    const USER = 'user';
    const COMMENT = 'comment';
    const LEAD = 'lead';
    const ISSUE = 'case';
    const PROJECT = 'project';
    const NOTE = 'note';
    const TIME = 'time';
    const PRODUCT_FAMILY = 'product_family';
    const OPPORTUNITY = 'opportunity';
    const PRODUCT = 'product';
    const INVOICE_TEMPLATE = 'invoice_templace';
    const DOCUMENT = 'infocenter_document';
    const ACTIVITY = 'activity';
    const APPROVAL = 'approval';
    const MEMBER = 'member';
    const SALES_ORDER = 'sales_order';
    const PRODUCT_REVIEW = 'product_review';
    const DASHBOARD = 'dashboard';
    const SALES_PAYMENT = 'sales_payment';
    const PROJECT_STORY = 'project_story';
    const FOLDER = 'folder';
    const FILE = 'file';
    const CALENDAR = 'calendar';
    const HTML_TEMPLATE = 'html_template';
    const MARKETING_CAMPAIGN = 'marketing_campaign';
    const SITE = 'cms_site';
    const PAGE = 'cms_page';
    const PAGE_TEMPLATE = 'cms_page_template';
    const HTML_SNIPPET = 'cms_snippet';
    const PHONE_CALL = 'phone_call';
    const STATUS_UPDATE = 'status_update';
    const REMINDER = 'reminder';
    const WORKFLOW = 'workflow';
    const WORKFLOW_ACTION = 'workflow_action';
    const USER_TEAM = 'user_team';
    const WORKER_JOB = 'worker_job';
    const DABOARD_WIDGET = 'dashboard_widget';
    const NOTIFICATION = 'notification';
    const TASK = 'task';
    const CONTACT_PERSONAL = 'contact_personal';
}
