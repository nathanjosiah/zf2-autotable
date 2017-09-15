# Configuration Options

Within the `ServiceManager` config key `auto_tables`:

key | type | description | required | default
--- | ---- | ----------- | -------- | -------
table_name | string | The name of the table in the DB. | true | None
table | string | The ServiceManager key to pull for the table class. Returned class must implement the `AutoTable\TableInterface` interface. | false | `AutoTable\BaseTable`
entity | string | Either a `ServiceManager` key or the Fully-Qualified Class Name of the entity to use. | false if only for a many-to-many mapping table, otherwise true | None
hydrator | string | Either a `ServiceManager` key or the Fully-Qualified Class Name of the hydrator to use. | false if only for a many-to-many mapping table, otherwise true | None
id_column | string | The name of the primary column in the DB for this table. | false | "id"
primary_property | string | The name of the primary property in the entity. | false | "id"
linked_tables | array of [`LinkedTableConfig`](#linkedtableconfig) | Each key should be the property to link to another table, and each value should be a [`LinkedTableConfig`](#linkedtableconfig) array. | false | null

## LinkedTableConfig

key | type | description | required | default
--- | ---- | ----------- | -------- | -------
alias_to | string | Use this if you want to map a friendlier name to a column. For example, mapping "author" to "authorId". Specify the LinkedTableConfig you want to alias. **If specified, all other keys are ignored.** | false | null
type | string Enum | May be `one_to_one`, `one_to_many`, `many_to_many`. Depending on which of these is set, the required settings change. | true | None
name | string | The table to link to. This is a key in the `auto_tables` config, not a DB table name. | true | None
should_save | bool | When using `alias_to` or a `one_to_many` or `many_to_many` type, the key is automatically removed from the data before saving to the DB as these properties typically don't represent real columns. To turn off this behavior for single properties, set this to true. | false | false
remote_column | string | The DB column name in the remote table to link to. | true if `one_to_many` or `many_to_many` | None
local_property | string | The entity property use when linking to the remote table. | true if `one_to_many` or `many_to_many` | None
local_column | string | The DB column to link to the mapping table for the primary table. | true if `many_to_many` | None
local_mapping_column | string | The DB column of the mapping table to link to the local column. | true if `many_to_many` | None
remote_mapping_column | string | The DB column of the mapping table to link to the remote column. | true if `many_to_many` | None

To help illustrate the `many_to_many` options, here is a diagram for an `articles` table:

`articles`:

id (local_column) | title
-- | --
1 | Sweet Baby Ray's is the best!

`authors`:

id (remote_column) | Name
-- | --
2 | Nathan Smith

`articles_authors_map`:

article_id (local_mapping_column) | author_id (remote_mapping_column)
-- | --
1 | 2



