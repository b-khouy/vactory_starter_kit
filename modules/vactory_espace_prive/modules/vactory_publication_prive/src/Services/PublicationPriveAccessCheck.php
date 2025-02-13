<?php

namespace Drupal\vactory_publication_prive\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\node\Entity\Node;

/**
 * Class PublicationPriveAccessCheck.
 *
 * @package Drupal\vactory_publication_prive\Services
 */
class PublicationPriveAccessCheck {

  /**
   * The current user ID.
   *
   * @var string
   */
  protected $currentUserId;

  /**
   * Current user object.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $currentUserObject;

  /**
   * Entity manager object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * FAQPriveAccessCheck constructor.
   *
   * @param \Drupal\Core\Session\AccountProxy $account
   *   Current user account.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager object.
   */
  public function __construct(AccountProxy $account, EntityTypeManagerInterface $entityTypeManager) {
    $this->currentUserId = $account->id();
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUserObject = $this->entityTypeManager->getStorage('user')
      ->load($this->currentUserId);
  }

  /**
   * Check if the given FAQ prive node is accessible or not.
   */
  public function isAccessible(Node $node) {
    if ($node->bundle() == 'vactory_publication_prive') {
      $is_accessible = FALSE;
      $current_user_id = $this->currentUserId;
      $user_groups = $this->currentUserObject->get('field_user_groups')->getValue();
      $publication_groups = $node->get('field_groupes_utilisateurs')->getValue();
      if (!empty($publication_groups) && !empty($user_groups)) {
        $user_groups = array_map(function ($el) {
          return $el['target_id'];
        }, $user_groups);
        $publication_groups = array_map(function ($el) {
          return $el['target_id'];
        }, $publication_groups);

        if (count(array_intersect($user_groups, $publication_groups)) > 0) {
          $is_accessible = TRUE;
        }
      }
      if (!$is_accessible) {
        $extra_users = $node->get('field_publication_prive_users')->getValue();
        if (!empty($extra_users)) {
          $extra_users = array_map(function ($el) {
            return $el['target_id'];
          }, $extra_users);
          if (in_array($current_user_id, $extra_users)) {
            $is_accessible = TRUE;
          }
        }
      }
      return $is_accessible;
    }

    return NULL;
  }

}
