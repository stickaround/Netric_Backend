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
    const ACTIVITY = 'activity';
    const APPROVAL = 'approval';
    const CALENDAR = 'calendar';
    const CALENDAR_EVENT = 'calendar_event';
    const CALENDAR_EVENT_PROPOSAL = 'calendar_event_proposal';
    const CHAT_ROOM = 'chat_room';
    const CHAT_MESSAGE = 'chat_message';
    const COMMENT = 'comment';
    const CONTACT = 'contact';
    const CONTACT_PERSONAL = 'contact_personal';
    const CONTENT_FEED = 'content_feed';
    const CONTENT_FEED_POST = 'content_feed_post';
    const DASHBOARD = 'dashboard';
    const DASHBOARD_WIDGET = 'dashboard_widget';
    const DISCUSSION = 'discussion';
    const DOCUMENT = 'infocenter_document';
    const EMAIL_ACCOUNT = 'email_account';
    const EMAIL_CAMPAIGN = 'email_campaign';
    const EMAIL_MESSAGE = 'email_message';
    const EMAIL_MESSAGE_ATTACHMENT = 'email_message_attachment';
    const EMAIL_THREAD = 'email_thread';
    const FILE = 'file';
    const FOLDER = 'folder';
    const HTML_TEMPLATE = 'html_template';
    const HTML_SNIPPET = 'cms_snippet';
    const INVOICE = 'invoice';
    const INVOICE_TEMPLATE = 'invoice_template';
    const LEAD = 'lead';
    const LOG = 'log';
    const MARKETING_CAMPAIGN = 'marketing_campaign';
    const MEMBER = 'member';
    const NOTE = 'note';
    const NOTIFICATION = 'notification';
    const OPPORTUNITY = 'opportunity';
    const PAGE = 'cms_page';
    const PAGE_TEMPLATE = 'cms_page_template';
    const PHONE_CALL = 'phone_call';
    const PRODUCT = 'product';
    const PRODUCT_FAMILY = 'product_family';
    const PRODUCT_REVIEW = 'product_review';
    const PROJECT = 'project';
    const PROJECT_MILESTONE = 'project_milestone';
    const REMINDER = 'reminder';
    const REPORT = 'report';
    const SALES_ORDER = 'sales_order';
    const SALES_PAYMENT = 'sales_payment';
    const SALES_PAYMENT_PROFILE = 'payment_profile';
    const STATUS_UPDATE = 'status_update';
    const SITE = 'cms_site';
    const TASK = 'task';
    const TICKET = 'ticket';
    const TICKET_CHANNEL = 'ticket_channel';
    const TIME = 'time';
    const USER = 'user';
    const USER_TEAM = 'user_team';
    const USER_REACTION = 'user_reaction';
    const WORKFLOW = 'workflow';
    const WORKFLOW_ACTION = 'workflow_action';
    const WORKFLOW_ACTION_SCHEDULED = 'workflow_action_scheduled';
    const WORKFLOW_INSTANCE = 'workflow_instance';
    const WORKER_JOB = 'worker_job';
}
