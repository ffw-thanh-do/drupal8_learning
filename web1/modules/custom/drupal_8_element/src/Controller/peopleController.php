<?php

namespace Drupal\drupal_8_element\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\drupal_8_element\Entity\peopleInterface;

/**
 * Class peopleController.
 *
 *  Returns responses for People routes.
 */
class peopleController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a People  revision.
   *
   * @param int $people_revision
   *   The People  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($people_revision) {
    $people = $this->entityManager()->getStorage('people')->loadRevision($people_revision);
    $view_builder = $this->entityManager()->getViewBuilder('people');

    return $view_builder->view($people);
  }

  /**
   * Page title callback for a People  revision.
   *
   * @param int $people_revision
   *   The People  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($people_revision) {
    $people = $this->entityManager()->getStorage('people')->loadRevision($people_revision);
    return $this->t('Revision of %title from %date', ['%title' => $people->label(), '%date' => format_date($people->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a People .
   *
   * @param \Drupal\drupal_8_element\Entity\peopleInterface $people
   *   A People  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(peopleInterface $people) {
    $account = $this->currentUser();
    $langcode = $people->language()->getId();
    $langname = $people->language()->getName();
    $languages = $people->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $people_storage = $this->entityManager()->getStorage('people');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $people->label()]) : $this->t('Revisions for %title', ['%title' => $people->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all people revisions") || $account->hasPermission('administer people entities')));
    $delete_permission = (($account->hasPermission("delete all people revisions") || $account->hasPermission('administer people entities')));

    $rows = [];

    $vids = $people_storage->revisionIds($people);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\drupal_8_element\peopleInterface $revision */
      $revision = $people_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $people->getRevisionId()) {
          $link = $this->l($date, new Url('entity.people.revision', ['people' => $people->id(), 'people_revision' => $vid]));
        }
        else {
          $link = $people->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.people.translation_revert', ['people' => $people->id(), 'people_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.people.revision_revert', ['people' => $people->id(), 'people_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.people.revision_delete', ['people' => $people->id(), 'people_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['people_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
