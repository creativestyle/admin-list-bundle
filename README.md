Admin List Budle
================

## Setup

You might need to add to your `config/packages/framework.yaml`:
```
    templating:
        engines: twig
```

## Templates

The templates are css-framework agnostic, you should override them (via symfony mechanism or by changing their config),
extend the markup and style it.

## Quickstart

### In your controller

Get the `FilterManager` instance. _This is a non-shared service, so you get new instance
every time._ 

```php
/** @var FilterManager $filters */
$filters = $this->get('creativestyle_admin_list.filter_manager');
```

Similarly to form builder it exposes a fluent interface, so you can register new filters:

```php
$filters
    ->registerFilter(new SomeFilter('filterid', ['some_config' => 'value']);
    ->registerFilter(new SomeFilter('otherfilterid', ['some_config' => 'value'])
;
```

You can also register a paginator:

```php
$filters
    ->setPaginator(new SimplePaginator([
        'order_by' => 'location.address.name',
    ]))
;
```

When you're done with configuration create a `QueryBuilder` and apply the filters, then 
fetch the results:

```php
$qb = $repository->createQueryBuilder('product');
$filters->applyFilters($qb);

$results = $this->getFilterManager()->getPaginatedResults($queryBuilder);
```

You will need both the results and `FilterManager` in your template:

```php
return $this->render('template.html.twig', [
    'results' => $results,
    'filters' => $filters,
]);
```

### In your template

You can render each filter by id:

```twig
{{ filters.renderFilter('filterid')|raw }}
```

You can also render the pagination controls like this:

```twig
{{ results.renderPaginationControls()|raw }}
```

For rendering the results themselves use something like this:

```twig
<table>
    <thead>
        <tr>
            <th>Id</th>
            <th>{{ filters.renderSortControls('entityalias.fieldname', 'Column Title')|raw }}</th>
            <th>{{ filters.renderSortControls('entityalias.createdAt', 'Created At')|raw }}</th>
        </tr>
    </thead>
    
    {# The result object is iterable #}
    <tbody>
        {% for product in products %}
            <tr>
                <td>{{ product.id }}</td>
                <td>{{ product.fieldname }}</td>
                <td>{{ product.createdAt|date }}</td>
            </tr>
        {% endfor %}
    </tbody>
</table>
```

And that's it! You've got a full-featured list UI with pagination, sorting and searching.

## Filter reference

Beside the built-in filters you can add your own by extending the base filters
or the base ones like `AbstractFilter`. Please look at the code - it's really simple.

### AbstractFilter

Base for other filters, not for direct usage.

__Options__

`callback`

Callable. If provided then it's used to apply the filter on the `QueryBuilder`.
If not provided then the internal filtering mechanism is used.

`default_value`

Default value of the filter.

`label`

Label, by default created by humanizing the filter id.

`parameters`

Parameters which will be directly passed into the template

`joins`

The joins to add to query if this filter is applied.

Accepts an array of joins where each join is an array of this form:
```
['rootEntityAlias.fieldname', 'alias']
```

`template` 

The template used for rendering.

### DateFilter

_Extends `AbstractFilter`_

Presents a datepicker field.

__Options__

`empty_label`

Label to use when nothing is selected.

`empty_value`

Value when nothing is selected.

`strategy`

By default EQUAL strategy is used.

One of:

- DateFilter::STRATEGY_EQUAL
- DateFilter::STRATEGY_GREATHER_THAN
- DateFilter::STRATEGY_LESS_THAN
- DateFilter::STRATEGY_GREATHER_THAN_OR_EQUAL
- DateFilter::STRATEGY_LESS_THAN_OR_EQUAL

`field`

The entity field that the filter will be applied to. 

### StringFilter 

_Extends `AbstractFilter`_

Present a text input. Allows searching multiple fields, using LIKE and more.

__Options__

`concat`

If true then the fields are concatenated before being searched.

`wildcard`

If true then a wildcard `*` is allowed (will be substitued with `%` in the query). 

`fields`

One filed as string or a list of fields to search.

`exact`

If true, then an exact match is needed (`LIKE` is not used).
If `concat` or `wildcard` is enabled then this is set to `false` by default. 
Otherwise the default is `true`.

### Choice filter

_Extends `AbstractFilter`_

Presents a dropdown list or tabs.

__Options__

`empty_label`

Label to use when nothing is selected.

`empty_value`

Value when nothing is selected.

`choices`

Array of choices of the form `[value => label]`.

`callback`

The callback is mandatory for this filter.

Use it like this:

```php
new ChoiceFilter('id', [
    'choices' => [
        'black' => 'Dark',
        'white' => 'Light',
    ],
    'callback' => function (QueryBuilder $qb, $value) {
        if ($value) {
            $qb->andWhere('product.color = :choice')
               ->setParameter('choice', $value);
        }
    }
]);
```

`disabled`

Disable the filter?

`tabbed`

If true then tabs are used instead of dropdown list.

## Paginator reference

### AbstractPaginator

Base paginator, not for direct usage.

TBD

### SimplePaginator

Presents user with a sliding paginator which may be slow with a lot of items because
it has to do `COUNT` queries.

options TBD

### OffsetPaginator

Paginator for very big lists. It only allows _next_ and _previous_ navigation without
giving user the information of how many elements/pages are on the list.

options TBD