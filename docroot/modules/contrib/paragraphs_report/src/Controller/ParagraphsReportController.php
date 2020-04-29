<?php

namespace Drupal\paragraphs_report\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\paragraphs_report\ParagraphsReport;

/**
 * paragraphs_report methods
 */
class ParagraphsReportController extends ControllerBase {

  /**
   * @var \Drupal\paragraphs_report\ParagraphsReport
   */
  protected $paragraphsReport;

  /**
   * Constructs the controller object.
   *
   * @param \Drupal\paragraphs_report\ParagraphsReport
   */
  public function __construct(ParagraphsReport $paragraphsReport) {
    $this->paragraphsReport = $paragraphsReport;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('paragraphs_report.report')
    );
  }

  /**
   * Return a rendered table ready for output.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function showReport() {
    return $this->paragraphsReport->showReport();
  }

  /**
   * Batch API starting point.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   * @throws
   */
  public function batchRunReport() {
    return $this->paragraphsReport->batchSetup();
  }

}
