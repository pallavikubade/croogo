<?php

App::uses('CakeEventListener', 'Event');

/**
 * Nodes Event Handler
 *
 * @category Event
 * @package  Croogo.Nodes.Event
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.croogo.org
 */
class NodesEventHandler implements CakeEventListener {

/**
 * implementedEvents
 */
	public function implementedEvents() {
		return array(
			'Croogo.bootstrapComplete' => array(
				'callable' => 'onBootstrapComplete',
			),
			'Controller.Links.setupLinkChooser' => array(
				'callable' => 'onSetupLinkChooser',
			),
		);
	}

/**
 * onBootstrapComplete
 */
	public function onBootstrapComplete($event) {
		if (CakePlugin::loaded('Comments')) {
			App::uses('Comment', 'Comments.Model');
			Croogo::hookBehavior('Node', 'Comments.Commentable');
			Croogo::hookModelProperty('Comment', 'belongsTo', array(
				'Node' => array(
					'className' => 'Nodes.Node',
					'foreignKey' => 'foreign_key',
					'counterCache' => true,
					'counterScope' => array(
						'Comment.model' => 'Node',
						'Comment.status' => Comment::STATUS_APPROVED,
					),
				),
			));
		}
		if (CakePlugin::loaded('Taxonomy')) {
			Croogo::hookBehavior('Node', 'Taxonomy.Taxonomizable');
		}
		if (CakePlugin::loaded('Meta')) {
			Croogo::hookBehavior('Node', 'Meta.Meta');
		}
	}

/**
 * Setup Link chooser values
 *
 * @return void
 */
	public function onSetupLinkChooser($event) {
		$Type = ClassRegistry::init('Taxonomy.Type');
		$types = $Type->find('all', array(
			'fields' => array('alias', 'title', 'description'),
		));
		$linkChoosers = array();
		foreach ($types as $type) {
			$linkChoosers[$type['Type']['title']] = array(
				'title' => $type['Type']['title'],
				'description' => $type['Type']['description'],
				'url' => array(
					'plugin' => 'nodes',
					'controller' => 'nodes',
					'action' => 'index',
					'?' => array(
						'type' => $type['Type']['alias'],
						'chooser' => 1,
						'KeepThis' => true,
						'TB_iframe' => true,
						'height' => 400,
						'width' => 600
					)
				)
			);
		}
		Croogo::mergeConfig('Menus.linkChoosers', $linkChoosers);
	}

}
