#Install instructions

##composer.json
```
"require": {
+   "cakephp/acl": "dev-master",
+   "pedrovalmor/cakephp3-acl-plugin": "master"
},
```
```
$> composer update
```

##config/bootstrap.php
```
Plugin::load('Acl', ['bootstrap' => true]);
```

##AppController.php
```
public function initialize()
{
    parent::initialize();
    //set locale format date
    Type::build('datetime')->useLocaleParser()->setLocaleFormat('dd-mm-yyyy');
    Time::setToStringFormat('dd/MM/YYYY');
    //acl
    $this->loadComponent('Acl.Acl');
    $this->loadComponent('RequestHandler');
    $this->loadComponent('Flash');
    $this->loadComponent('Auth', [
        'authorize' => 'Controller',
        'unauthorizedRedirect' => false,
        'loginAction' => [
            'controller' => 'users', 'action' => 'login'
        ],
        'logoutRedirect' => [
            'controller' => 'users', 'action' => 'login'
        ],
        'loginRedirect' => [
            'controller' => 'pages', 'action' => 'index'
        ],
        'authError' => 'Did you really think you are allowed to see that?',
        'authenticate' => [
            'Form' => [
                'userModel' => 'users',
                'fields' => ['username' => 'email', 'password' => 'pass']
            ]
        ],
        'storage' => 'Session'
    ]);
}
```
```
public function isAuthorized($user)
{
    $acl = new AclComponent(new ComponentRegistry);
    $return = $acl->check(['Users' => ['id' => $user['id']]], $this->request->controller . '/' . $this->request->action);
    if ($return) {
        //$this->viewBuilder()->layout('admin'); // if you have admin template differ of default
        return true;
    } else {
        return false;
    }
}
```

##GroupsController.php
```
public function edit($id = null)
{
    $group = $this->Groups->get($id, [
        'contain' => []
    ]);

    $this->loadComponent('pedrovalmor/AclManager.AclManager');
    $EditablePerms = $this->AclManager->getFormActions();

    if ($this->request->is(['patch', 'post', 'put'])) {
        $group = $this->Groups->patchEntity($group, $this->request->data);
        if ($this->Groups->save($group)) {

            $this->eventManager()->on(new PermissionsEditor());
            $perms = new Event('Permissions.editPerms', $this, [
                'Aro' => $group,
                'datas' => $this->request->data
            ]);
            $this->eventManager()->dispatch($perms);

            $this->Flash->success(__('The group has been saved.'));
            return $this->redirect(['action' => 'index']);
        } else {
            $this->Flash->error(__('The group could not be saved. Please, try again.'));
        }
    }
    $this->set(compact('group', 'EditablePerms'));
    $this->set('_serialize', ['group', 'EditablePerms']);
}
```

##Entity/Group.php
```
public function parentNode()
{
    return null;
}
```

##Entity/User.php
```
protected function _setPassword($password)
{
    return (new DefaultPasswordHasher)->hash($password);
}
public function parentNode()
{
    if (!$this->id) {
        return null;
    }
    if (isset($this->group_id)) {
        $group_id = $this->group_id;
    } else {
        $users_table = TableRegistry::get('Users');
        $user = $users_table->find('all', ['fields' => ['group_id']])->where(['id' => $this->id])->first();
        $group_id = $user->group_id;
    }
    if (!$group_id) {
        return null;
    }

    return ['Groups' => ['id' => $group_id]];
}
```

##Table/UsersTable.php
##Table/GroupsTable.php
```
public function initialize()
{
+    $this->addBehavior('Acl.Acl', ['type' => 'requester']);
}
```

##Groups/edit.ctp
```
<?php foreach ($EditablePerms as $Acos) : ?>
    <?php foreach ($Acos as $controllerPath => $actions) : ?>
        <?php if (!empty($actions)) : ?>
            <h4><?= __($controllerPath); ?></h4>
            <?php foreach ($actions as $action) : ?>
                <?php $check = ($this->AclManager->checkGroup($group, 'App/' . $controllerPath . '/' . $action)) ? 'checked' : null; ?>
                <?= $this->Form->checkbox('App/' . $controllerPath . '/' . $action, [$check]); ?>
                <?= $this->Form->label('App/' . $controllerPath . '/' . $action, $action); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endforeach; ?>
```

##ACL migrate DB
```
$> bin/cake Migrations.migrations migrate -p Acl
```

##ACO update
```
$> bin/cake acl_extras aco_update
```