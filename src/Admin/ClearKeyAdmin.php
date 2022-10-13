<?php

namespace QuinnInteractive\ClearKey\Admin;

use QuinnInteractive\ClearKey\Extensions\ClearKeyExtension;
use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldFooter;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\ArrayData;

class ClearKeyAdmin extends LeftAndMain implements PermissionProvider
{
    private static $allowed_actions = [
        'doClearAll',
    ];
    private static $menu_icon = 'public/_resources/vendor/quinninteractive/silverstripe-clearkey/client/images/key.svg';
    private static $menu_title = 'Clear Keys';
    private static $model_importers = []; // no CSV import
    private static $required_permission_codes = 'CMS_ACCESS_ClearKeyAdmin';
    private static $url_segment = 'clearkey';

    /**
     * @TODO move this into a GridField_ActionProvider
     * @return HTTPResponse|string
     */
    public function doClearAll()
    {
        $cache = ClearKeyExtension::getCache();
        $cache->clear();
        return $this->redirect($this->Link() . '?m=' . microtime(1));
    }

    public function getEditForm($id = null, $fields = null): Form
    {
        // List all reports
        if (null === $fields) {
            $fields = new FieldList();
        }
        $gridFieldConfig = GridFieldConfig::create()->addComponents(
            new GridFieldSortableHeader(),
            new GridFieldDataColumns(),
            new GridFieldFooter()
        );
        $gridField = new GridField('ClearKeys', null, self::getClearKeys(), $gridFieldConfig);
        $config = $gridField->getConfig();
        /** @var GridFieldDataColumns $columns */
        $columns = $config->getComponentByType('SilverStripe\\Forms\\GridField\\GridFieldDataColumns');
        $columns->setDisplayFields([
            'Title' => 'Title',
            'Draft' => 'Draft',
            'Live'  => 'Live',
        ]);

        // add the delete buttons
        $config->addComponent(new ClearKeyGridFieldDeleteButton());

        $gridField->addExtraClass('all-reports-gridfield');
        $fields->push($gridField);

        // @TODO move this into a GridField_ActionProvider
        $actions = new FieldList(
            new FormAction('doClearAll', 'Clear All')
        );
        $form = new Form($this, 'EditForm', $fields, $actions);
        $form->addExtraClass('panel panel--padded panel--scrollable cms-edit-form cms-panel-padded' . $this->BaseCSSClasses());
        $form->loadDataFrom($this->request->getVars());
        $this->extend('updateEditForm', $form);
        /** @var Form */
        return $form;
    }

    public function Link($action = null)
    {
        return 'admin/' . Config::inst()->get(self::class, 'url_segment');
    }

    /**
     * @return string[][]
     *
     * @psalm-return array{CMS_ACCESS_ClearKeyAdmin: array{name: string, category: string, help: string}}
     */
    public function providePermissions()
    {
        return [
            "CMS_ACCESS_ClearKeyAdmin" => [
                'name' => _t(
                    'SilverStripe\\CMS\\Controllers\\CMSMain.ACCESS',
                    "Access to '{title}' section",
                    ['title' => static::menu_title()]
                ),
                'category' => _t('SilverStripe\\Security\\Permission.CMS_ACCESS_CATEGORY', 'CMS Access'),
                'help'     => _t(
                    __CLASS__ . '.ACCESS_HELP',
                    'Allow viewing of the cache key clearing section.'
                ),
            ],
        ];
    }

    public static function getClearKeys(): ArrayList
    {
        $orig_stage = Versioned::get_stage();
        $cache = ClearKeyExtension::getCache();
        $invalidators = Config::inst()->get(ClearKeyExtension::class, 'invalidators');
        $results = new ArrayList();
        $id = 1;
        foreach ($invalidators as $key => $list) {
            $data = ['ID' => $id, 'Title' => $key];
            Versioned::set_stage(Versioned::DRAFT);
            $data['Draft'] = $cache->get($key);
            Versioned::set_stage(Versioned::LIVE);
            $data['Live'] = $cache->get($key);
            $results[] = new ArrayData($data);
            $id++;
        }
        Versioned::set_stage($orig_stage);
        return $results;
    }
}
