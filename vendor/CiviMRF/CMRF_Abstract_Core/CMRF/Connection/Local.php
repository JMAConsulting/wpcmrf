<?php

/**
 * TODO
 *
 * @author Björn Endres, SYSTOPIA (endres@systopia.de)
 */

namespace CMRF\Connection;

use CMRF\Core\Call as Call;
use CMRF\Core\Connection as Connection;

class Local extends Connection {

  public function getType() {
    return 'local';
  }

  public function isReady() {
    return function_exists('civicrm_api3');
  }

  /**
   * execute the given call synchroneously
   * 
   * return call status
   */
  public function executeCall(Call $call) {
    $request = $this->getAPI3Params($call);
    if ($call->getEntity() == 'FormProcessor'  && $call->getAction() == "volunteer_application") {
      $yesno = [
        'type_in_english',
        'type_in_chinese',
        'employee_of_yeehong',
        'read_and_write_chinese',
        'criminal_offence',
        'driver',
        'interest_class_instructor',
        'breakfast_booth',
        'assistant_with_meals',
        'friendly_visiting',
        'office_and_clerical_duties',
        'speak_cantonese',
        'escort_shopping',
        'special_accomodation',
        'speak_english',
        'pa_nursing_home',
        'phone_chat',
        'kitchen_assistant',
        'car',
        'age_18_',
        'speak_mandarin',
        'reception_desk_assistant',
        'meal_delivery',
        'laundry_assistant',
        'pa_community_program',
        'i_consent',
        'read_and_write_english'
      ];
      foreach ($request as $key => $value) {
        if (is_array($value) && empty($value[0])) {
          unset($request[$key]);
        }
        if (in_array($key, $yesno) && empty($value)) {
          $request[$key] = "0";
        }
      }
    }
    if ($call->getEntity() == 'FormProcessor' && $call->getAction() == 'contact_information') {
	    $yesno = [
		    'age_18_',
		    'driving_license',
      ];
	    foreach ($request as $key => $value) {
		    if (is_array($value) && empty($value[0])) {
			    unset($request[$key]);
		    }
		    if (in_array($key, $yesno) && empty($value)) {
			    $request[$key] = "0";
		    }
      }	      
    } 
    try {
      $reply = civicrm_api3(
        $call->getEntity(),
        $call->getAction(),
        $request);      
    } catch (\Exception $e) {
      $call->setStatus(Call::STATUS_FAILED, $e->getMessage());
      return $call->getReply();
    }

    // Hack from CiviCRM core to make the reply behave similar as the remote API.
    // Meaning that a scalar value (a number, string etc.) should be wrapped in an array by the key result.
    if (is_scalar($reply)) {
      if (!$reply) {
        $reply = 0;
      }
      $reply = array(
        'is_error' => 0,
        'result' => $reply
      );
    }

    $call->setReply($reply);
    return $reply;
  }

}


