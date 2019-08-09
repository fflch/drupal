<?php

namespace Drupal\fflch_audit\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class AuditController.
 */
class AuditController extends ControllerBase {

  /**
   *
   * @return string
   *   Return json string.
   */
  public function audit() {
    $reportManager = \Drupal::service('plugin.manager.site_audit_report');
    $reportDefinitions = $reportManager->getDefinitions();

    //var_dump(\Drupal::config('site_audit.settings')->get('reports'));
    //$labels = ['codebase','database'];
    $labels = ['database'];

    $reports = [];
    foreach ($labels as $label) {
        $reports[] = $reportManager->createInstance($label);
    }

    /* all reports
    foreach ($reportDefinitions as $reportDefinition) {
      $reports[] = $reportManager->createInstance($reportDefinition['id']);
    }
    */

    $reports_json = [];
    foreach($reports as $report){
        $report_json = array(
          'percent' => $report->getPercent(),
          'label' => $report->getLabel(),
          'checks' => array(),
        );
        foreach ($report->getCheckObjects() as $check) {
          $report_json['checks'][get_class($check)] = array(
            'label' => $check->getLabel(),
            'description' => $check->getDescription(),
            'result' => $check->getResult(),
            'action' => $check->renderAction(),
            'score' => $check->getScore(),
          );
        }
        $reports_json[] = $report_json;
    }

    return new JsonResponse([
      'data' => $reports_json,
      'method' => 'GET',
    ]);
  }

}
