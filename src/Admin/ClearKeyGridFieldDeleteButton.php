<?php

namespace QuinnInteractive\ClearKey\Admin;

use QuinnInteractive\ClearKey\Extensions\ClearKeyExtension;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Versioned\Versioned;

class ClearKeyGridFieldDeleteButton implements GridField_ColumnProvider, GridField_ActionProvider
{
    public function augmentColumns($gridField, &$columns)
    {
        if (!in_array('Actions', $columns)) {
            $columns[] = 'Actions';
        }
    }

    public function getActions($gridField)
    {
        return ['deleteclearkey'];
    }

    public function getColumnAttributes($gridField, $record, $columnName)
    {
        return ['class' => 'grid-field__col-compact'];
    }

    public function getColumnContent($gridField, $record, $columnName)
    {
        $field = GridField_FormAction::create(
            $gridField,
            'CleraKeyAction' . $record->Title,
            'Clear',
            "deleteClearKey",
            [
                'ID'    => $record->ID,
                'Title' => $record->Title,
            ]
        );

        return $field->Field();
    }

    public function getColumnMetadata($gridField, $columnName)
    {
        if ($columnName == 'Actions') {
            return ['title' => ''];
        }
    }

    public function getColumnsHandled($gridField)
    {
        return ['Actions'];
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        if ($actionName == 'deleteclearkey') {
            ClearKeyExtension::invalidateClearKey($arguments['Title'], Versioned::DRAFT);
            ClearKeyExtension::invalidateClearKey($arguments['Title'], Versioned::LIVE);
            $gridField->setList(ClearKeyAdmin::getClearKeys());
            // output a success message to the user
            Controller::curr()->getResponse()->setStatusCode(
                200,
                'Key Cleared.'
            );
        }
    }
}
