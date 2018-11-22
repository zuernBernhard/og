<?php

namespace Drupal\Tests\og\Functional;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\og\Og;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the "Group" tab.
 *
 * @group og
 */
class GroupTabTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['node', 'og'];

  /**
   * Test entity group.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $group;

  /**
   * A group bundle name.
   *
   * @var string
   */
  protected $bundle1;

  /**
   * A group bundle name.
   *
   * @var string
   */
  protected $bundle2;

  /**
   * A non-author user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user1;

  /**
   * A non-author user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user2;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create bundles.
    $this->bundle1 = mb_strtolower($this->randomMachineName());
    $this->bundle2 = mb_strtolower($this->randomMachineName());

    // Create node types.
    $node_type1 = NodeType::create(['type' => $this->bundle1, 'name' => $this->bundle1]);
    $node_type1->save();

    $node_type2 = NodeType::create(['type' => $this->bundle2, 'name' => $this->bundle2]);
    $node_type2->save();

    // Define the first bundle as group.
    Og::groupTypeManager()->addGroup('node', $this->bundle1);

    // Create node author user.
    $this->user1 = $this->drupalCreateUser(['administer group']);

    // Create normal user.
    $this->user2 = $this->drupalCreateUser(['access content']);

    // Create nodes.
    $this->group = Node::create([
      'type' => $this->bundle1,
      'title' => $this->randomString(),
      'uid' => $this->user1->id(),
    ]);
    $this->group->save();

  }

  /**
   * Tests the formatter changes by user and membership.
   */
  public function testGroupTab() {
    $this->drupalLogin($this->user1);
    $this->drupalGet('group/node/' . $this->group->id() . '/admin');
    $this->assertResponse(200);
    $this->drupalLogout();
  }

  public function testGroupTabAccessDenied() {
    $this->drupalGet('group/node/' . $this->group->id() . '/admin');
    $this->assertResponse(403);
  }

}
