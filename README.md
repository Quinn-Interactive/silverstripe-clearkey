# ClearKey: A Silverstripe module to manage partial-cache invalidation

When using Silverstripe’s partial caching, the keys you use can usually be grouped into two types

1. Cache _identifier_ keys, which help tell one cache block from another.
2. Cache _clearing_ keys, which tell us when a block is stale.

Take this partial-cache block as an example:

```html
<% cached 'Promo', $ID, $Link, $Promo.LastEdited, $List('SiteTree').max('LastEdited'), $List('SiteTree').count() %>
...
<% end_cached %>
```

These _identifier_ keys help tell one promo from another:

- `'Promo'`
- `$ID`
- `$Link`

These _clearing_ keys help tell us when the content is stale:

- `Promo.LastEdited`
- `$List('SiteTree').max('LastEdited')`
- `$List('SiteTree').count()`

Partial caches do a great job with _identifier_ keys, but have to hit the database at every page load to calculate some of the _clearing_ keys, especially the aggregation ones.

ClearKey solves this by letting you manage the calculating of clearing of stale data whenever a relevant data object is saved, instead of on every page load. With ClearKey, the example cache block might look like this:

```html
<% cached $ClearKey('Promo'), $ID, $Link %>
...
<% end_cached %>
```

You define the clear keys with a corresponding config to describe which classes being updated should invalidate the keys:

```yaml
---
Name: clearkey-config
After:
  - '#corecache'
---
QuinnInteractive\ClearKey\Extensions\ClearKeyExtension:
  invalidators:
    Promo:
      - SilverStripe\App\Model\Promo
      - SilverStripe\CMS\Model\SiteTree
    AnotherKey:
      - SilverStripe\App\Model\Something
      - SilverStripe\CMS\Model\Etc
```

Current version: 0.0.0
