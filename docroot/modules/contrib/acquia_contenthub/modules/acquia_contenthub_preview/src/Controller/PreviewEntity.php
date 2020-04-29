<?php

namespace Drupal\acquia_contenthub_preview\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PreviewEntity extends ControllerBase {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $repository;

  /**
   * The class resolver.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  protected $resolver;

  /**
   * The entity view controller.
   *
   * @var \Drupal\Core\Entity\Controller\EntityViewController
   */
  protected $controller;

  public function __construct(EntityRepositoryInterface $repository, ClassResolverInterface $resolver) {
    $this->repository = $repository;
    $this->resolver = $resolver;
    $this->controller = $this->resolver->getInstanceFromDefinition('\Drupal\Core\Entity\Controller\EntityViewController');
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('class_resolver')
    );
  }

  public function preview(string $uuid) {
    $entity = $this->repository->loadEntityByUuid('node', $uuid);
    return $this->controller->view($entity);
  }

}
