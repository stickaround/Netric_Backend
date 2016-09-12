<?php
/**
 * Move the old calendar_events_recurring table to the new object_recurrence table
 */
use Netric\Entity\Recurrence\RecurrencePattern;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get("Netric/Db/Db");
$dm = $serviceManager->get("Netric/Entity/Recurrence/RecurrenceDataMapper");
$loader = $serviceManager->get("Netric/EntityLoader");

// Specify what object type we are going to move
$objType = "calendar_event";

// Query the calendar event recurrences
$result = $db->query("SELECT calendar_events.id AS event_id, type AS recur_type, calendar_events_recurring.date_start,
              calendar_events_recurring.date_end, calendar_events_recurring.all_day,
              interval, day AS day_of_month, month AS month_of_year,
              week_days[1] as day1, week_days[2] as day2, week_days[3] as day3,
			  week_days[4] as day4, week_days[5] as day5, week_days[6] as day6,
			  week_days[7] as day7
            FROM calendar_events_recurring
            INNER JOIN calendar_events ON (calendar_events_recurring.id = calendar_events.recur_id);");


for ($i = 0; $i < $db->getNumRows($result); $i++) {
    
    // Get the result row
    $row = $db->getRow($result, $i);

    // Setup the object type
    $row['obj_type'] = $objType;

    // Create a new instance of recurrence pattern
    $recurrencePattern = new RecurrencePattern();
    
    // Import the recurrence data
    $recurrencePattern->fromArray($row);

    if ($row['day1'] === 't')
        $recurrencePattern->setDayOfWeek(RecurrencePattern::WEEKDAY_SUNDAY, true);
    if ($row['day2'] === 't')
        $recurrencePattern->setDayOfWeek(RecurrencePattern::WEEKDAY_MONDAY, true);
    if ($row['day3'] === 't')
        $recurrencePattern->setDayOfWeek(RecurrencePattern::WEEKDAY_TUESDAY, true);
    if ($row['day4'] === 't')
        $recurrencePattern->setDayOfWeek(RecurrencePattern::WEEKDAY_WEDNESDAY, true);
    if ($row['day5'] === 't')
        $recurrencePattern->setDayOfWeek(RecurrencePattern::WEEKDAY_THURSDAY, true);
    if ($row['day6'] === 't')
        $recurrencePattern->setDayOfWeek(RecurrencePattern::WEEKDAY_FRIDAY, true);
    if ($row['day7'] === 't')
        $recurrencePattern->setDayOfWeek(RecurrencePattern::WEEKDAY_SATURDAY, true);

    // Save the recurrence pattern
    $recurId = $dm->save($recurrencePattern);

    // Load the calendar event that was associated to this recurrence and update the new recurrence id 
    $event = $loader->get($objType, $row['event_id']);

    // Set the new recurrence id
    $event->setValue("recur_id", $recurId, $recurId);

    // Save the calendar event
    $loader->save($event);
}