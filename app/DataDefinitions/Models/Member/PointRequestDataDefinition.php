<?php

namespace App\DataDefinitions\Models\Member;

use App\DataDefinitions\DataDefinition;
use App\View\Components\Ui\Icon;
use Illuminate\Database\Eloquent\Model;

/**
 * DataDefinition for listing, editing, and deleting point request links by a Member.
 *
 * Members can view their generated point request links, edit only the associated card and
 * whether the link is active, and delete their own request links.
 */
class PointRequestDataDefinition extends DataDefinition
{
    /**
     * Unique, URL‑friendly name for CRUD purposes.
     *
     * @var string
     */
    public $name = 'request-links';

    /**
     * The Eloquent model associated with the definition.
     *
     * @var Model
     */
    public $model;

    /**
     * The fields (columns) to display in the list and edit views.
     *
     * @var array
     */
    public $fields;

    /**
     * General settings for this data definition (query filter, allowed actions, etc.).
     *
     * @var array
     */
    public $settings;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Set the model – using the PointRequest model.
        $this->model = new \App\Models\PointRequest;

        // Define the columns for the list (and edit) view.
        $this->fields = [
            // Unique identifier as a clickable link.
            'unique_identifier' => [
                'text' => trans('common.link'),
                'type' => 'query',
                'searchable' => true,
                'classes::list' => 'w-2',
                'actions' => ['list'], // Not editable.
                'query' => function ($row) {
                    $url = route('member.request.points.send', ['request_identifier' => $row->unique_identifier]);

                    $iconComponent = new Icon('link', 'w-4 h-4 mr-2');
                    $icon = $iconComponent->render()->render(); // The second render() call is to render the View object to a string

                    return ' <a href="'.$url.'" class="inline-flex items-center whitespace-nowrap btn-dark btn-xs p-2"> '.trans('common.share_link').'</a>';
                },
            ],
            // Card selection.
            // Uses a belongsTo relation. If card_id is null, we show "Works with all cards".
            'card_id' => [
                'text' => trans('common.card'),
                'type' => 'query',
                'relation' => 'card',
                'relationKey' => 'cards.id',
                'relationValue' => 'cards.head',
                'relationModel' => new \App\Models\Card,
                'actions' => ['list', 'edit'],  // Allow editing this field.
                'sortable' => false,
                // Use a query callback to display a friendly message when no specific card is set.
                'query' => function ($row) {
                    return $row->card_id
                        ? $row->card->head
                        : trans('common.receive_points_for_all_cards');
                },
            ],
            // Whether the link is active.
            'is_active' => [
                'text' => trans('common.active'),
                'type' => 'boolean',
                'sortable' => true,
                'actions' => ['edit'], // Allow editing.
            ],
            // Usage count (read‑only).
            'usage_count' => [
                'text' => trans('common.uses'),
                'type' => 'number',
                'sortable' => true,
                'actions' => ['list'], // Not editable.
            ],
        ];

        // Define general settings.
        $this->settings = [
            'icon' => 'handshake',
            'title' => trans('common.request_links'),
            'guard' => 'member',
            'userMustOwnRecords' => true,
            'search' => false,

            // Custom link
            'customLink' => [
                'url' => route('member.request.points.generate'),
                'label' => trans('common.generate_request_link'),
                'icon' => 'plus',
            ],

            'onEmptyListRedirectTo' => route('member.request.points.generate'),

            // Allow listing, editing, and deleting.
            'actions' => [
                'subject_column' => 'unique_identifier',
                'list' => true,
                'insert' => false,
                'edit' => false,
                'delete' => true,
                'view' => false,
                'export' => false,
            ],
            'itemsPerPage' => 10,
            'orderByColumn' => 'created_at',
            'orderDirection' => 'desc',
        ];
    }

    /**
     * Retrieve data based on fields.
     */
    public function getData(
        ?string $dataDefinitionName = null,
        string $dataDefinitionView = 'list',
        array $options = [],
        ?Model $model = null,
        array $settings = [],
        array $fields = []
    ): array {
        return parent::getData(
            $this->name,
            $dataDefinitionView,
            $options,
            $this->model,
            $this->settings,
            $this->fields
        );
    }

    /**
     * Parse settings.
     */
    public function getSettings(array $settings): array
    {
        return parent::getSettings($this->settings);
    }
}
