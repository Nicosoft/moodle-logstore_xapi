<?php

namespace transformer\events\mod_scorm;

use transformer\utils as utils;

function status_submitted(array $config, \stdClass $event) {
    $repo = $config['repo'];
    $user = $repo->read_record_by_id('user', $event->userid);
    $course = $repo->read_record_by_id('course', $event->courseid);
    $lang = utils\get_course_lang($course);

    $unserialized_cmi = unserialize($event->other);
    $attempt = $unserialized_cmi['attemptid'];
    $scorm_scoes_tracks = $config['repo']->read_store_records('scorm_scoes_track', [
        'userid' => $userid,
        'scormid' => $event->objectid,
        'scoid' => $event->contextinstanceid,
        'attempt' => $unserialized_cmi['attemptid']
    ]);

    return [[
        'actor' => utils\get_user($config, $user),
        'verb' => utils\get_scorm_verb($scorm_scoes_tracks, $lang),
        'object' => utils\get_activity\module($config, $event, $lang),
        'timestamp' => utils\get_event_timestamp($event),
        'context' => [
            'platform' => $config['source_name'],
            'language' => $lang,
            'extensions' => [
                utils\info_extension => utils\get_info($config, $event),
            ],
            'contextActivities' => [
                'grouping' => [
                    utils\get_activity\site($config),
                    utils\get_activity\course($config, $course),
                ],
                'category' => [
                    utils\get_activity\source($config),
                ]
            ],
        ]
    ]];
}