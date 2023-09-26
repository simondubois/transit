<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW `itineraries` AS

            SELECT
                CONCAT(
                    `previous_events`.`location`,
                    IFNULL(`next_events`.`location`, `accounts`.`default_location`),
                    `previous_events`.`end`,
                    IFNULL(`next_events`.`start`, DATE_FORMAT(`previous_events`.`end`, '%Y-%m-%d 23:59:59'))
                ) AS `id`,
                `previous_calendars`.`account_id`,
                `previous_events`.`id` AS `previous_event_id`,
                `next_events`.`id` AS `next_event_id`,
                `previous_events`.`location` AS `departure`,
                IFNULL(`next_events`.`location`, `accounts`.`default_location`) AS `arrival`,
                `previous_events`.`end` AS `start`,
                IFNULL(`next_events`.`start`, DATE_FORMAT(`previous_events`.`end`, '%Y-%m-%d 23:59:59')) AS `end`
            FROM `events` AS `previous_events`
            LEFT JOIN `calendars` AS `previous_calendars` ON `previous_calendars`.`id` = `previous_events`.`calendar_id`
            LEFT JOIN `accounts` ON `accounts`.`id` = `previous_calendars`.`account_id`
            LEFT JOIN `events` AS `next_events` ON `next_events`.`id` = (
                SELECT `events`.`id`
                FROM `events`
                LEFT JOIN `calendars` ON `calendars`.`id` = `events`.`calendar_id`
                WHERE
                    `calendars`.`account_id` = `previous_calendars`.`account_id`
                    AND DATE(`events`.`start`) = DATE(`previous_events`.`end`)
                    AND `events`.`start` >= `previous_events`.`end`
                    AND `events`.`id` <> `previous_events`.`id`
                ORDER BY `events`.`start` ASC
                LIMIT 1
            )
            WHERE
                NOT EXISTS (
                    SELECT *
                    FROM `events`
                    LEFT JOIN `calendars` ON `calendars`.`id` = `events`.`calendar_id`
                    WHERE
                        `calendars`.`account_id` = `previous_calendars`.`account_id`
                        AND `previous_events`.`id` <> `events`.`id`
                        AND `previous_events`.`end` BETWEEN `events`.`start` AND `events`.`end`
                )
                AND (`next_events`.`start` <> `previous_events`.`end` OR `next_events`.`start` IS NULL)
                AND TIME(`previous_events`.`end`) < '23:59:59'

            UNION

            SELECT
                CONCAT(
                    `accounts`.`default_location`,
                    `next_events`.`location`,
                    DATE_FORMAT(`next_events`.`start`, '%Y-%m-%d 00:00:00'),
                    `next_events`.`start`
                ) AS `id`,
                `next_calendars`.`account_id`,
                null AS `previous_event_id`,
                `next_events`.`id` AS `next_event_id`,
                `accounts`.`default_location` AS `departure`,
                `next_events`.`location` AS `arrival`,
                DATE_FORMAT(`next_events`.`start`, '%Y-%m-%d 00:00:00') AS `start`,
                `next_events`.`start` AS `end`
            FROM `events` AS `next_events`
            LEFT JOIN `calendars` AS `next_calendars` ON `next_calendars`.`id` = `next_events`.`calendar_id`
            LEFT JOIN `accounts` ON `accounts`.`id` = `next_calendars`.`account_id`
            WHERE
                NOT EXISTS (
                    SELECT *
                    FROM `events`
                    LEFT JOIN `calendars` ON `calendars`.`id` = `events`.`calendar_id`
                    WHERE
                        `calendars`.`account_id` = `next_calendars`.`account_id`
                        AND `next_events`.`id` <> `events`.`id`
                        AND `next_events`.`start` BETWEEN `events`.`start` AND `events`.`end`
                )
                AND NOT EXISTS (
                    SELECT *
                    FROM `events`
                    LEFT JOIN `calendars` ON `calendars`.`id` = `events`.`calendar_id`
                    WHERE
                        `calendars`.`account_id` = `next_calendars`.`account_id`
                        AND DATE(`events`.`end`) = DATE(`next_events`.`start`)
                        AND `events`.`start` < `next_events`.`start`
                )
                AND TIME(`next_events`.`start`) > '00:00:00';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW `itineraries`");
    }
};
