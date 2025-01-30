<template>
  <div class="relative overflow-hidden overflow-x-auto HENK">
    <table
      v-if="resources.length > 0"
      class="w-full divide-y divide-gray-100 dark:divide-gray-700"
      dusk="resource-table-tester"
    >
      <ResourceTableHeader
        :resource-name="resourceName"
        :fields="fields"
        :should-show-column-borders="shouldShowColumnBorders"
        :should-show-checkboxes="shouldShowCheckboxes"
        :sortable="sortable"
        @order="requestOrderByChange"
        @reset-order-by="resetOrderBy"
      />
      <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
        <ResourceTableRow
          v-for="(resource, index) in resources"
          @actionExecuted="$emit('actionExecuted')"
          :actions-are-available="actionsAreAvailable"
          :actions-endpoint="actionsEndpoint"
          :checked="selectedResources.indexOf(resource) > -1"
          :click-action="clickAction"
          :delete-resource="deleteResource"
          :key="`${resource.id.value}-items-${index}`"
          :relationship-type="relationshipType"
          :resource-name="resourceName"
          :resource="resource"
          :restore-resource="restoreResource"
          :selected-resources="selectedResources"
          :should-show-checkboxes="shouldShowCheckboxes"
          :should-show-column-borders="shouldShowColumnBorders"
          :table-style="tableStyle"
          :testId="`${resourceName}-items-${index}`"
          :update-selection-status="updateSelectionStatus"
          :via-many-to-many="viaManyToMany"
          :via-relationship="viaRelationship"
          :via-resource-id="viaResourceId"
          :via-resource="viaResource"
        />
      </tbody>
        <tfoot v-if="calculate.length > 0" class="bg-gray-50 dark:bg-gray-800">
            <tr v-if="!footerSettings.hideHeader">
                <th class="w-16" v-if="shouldShowCheckboxes">&nbsp;</th>
                <th v-for="field in fields" v-bind:key="`totals-header-${field.uniqueKey}`" :class="`text-${field.totalsTitleAlignment}`" class="px-2 tracking-wide text-gray-500 uppercase text-xxs whitespace-nowrap">
                    {{field.title}}
                </th>
                <!-- Actions, View, Edit, Delete -->
                <th>&nbsp;</th>
            </tr>

            <tr>
                <th class="w-16 bg-gray-100 dark:bg-gray-700" v-if="shouldShowCheckboxes">&nbsp;</th>
                <th v-for="field in fields" v-bind:key="`totals-value-${field.uniqueKey}`" :class="`text-${field.totalsAlignment}`" class="px-2 text-base tracking-wide text-gray-500 uppercase bg-gray-100 whitespace-nowrap dark:bg-gray-700">
                    {{field.prefix}}
                    <span v-if="field.calculate_method">{{calculate.find(o=>o.indexName===field.sortableUriKey)?calculate.find(o=>o.indexName===field.sortableUriKey).value:0}}</span>
                    {{field.postfix}}
                </th>
                <!-- Actions, View, Edit, Delete -->
                <th class="bg-gray-100 dark:bg-gray-700">&nbsp;</th>
            </tr>
      </tfoot>
    </table>
  </div>
</template>

<script>
import { InteractsWithResourceInformation } from 'laravel-nova'

export default {
  emits: ['actionExecuted', 'delete', 'restore', 'order', 'reset-order-by'],

  mixins: [InteractsWithResourceInformation],

  props: {
    authorizedToRelate: { type: Boolean, required: true },
    resourceName: { default: null },
    resources: { default: [] },
    singularName: { type: String, required: true },
    selectedResources: { default: [] },
    selectedResourceIds: {},
    shouldShowCheckboxes: { type: Boolean, default: false },
    actionsAreAvailable: { type: Boolean, default: false },
    viaResource: { default: null },
    viaResourceId: { default: null },
    viaRelationship: { default: null },
    relationshipType: { default: null },
    updateSelectionStatus: { type: Function },
    actionsEndpoint: { default: null },
    sortable: { type: Boolean, default: false },
  },

  data: () => ({
    selectAllResources: false,
    selectAllMatching: false,
    resourceCount: null,
    calculate: [],
    footerSettings: [],
  }),

  methods: {
    /**
     * Delete the given resource.
     */
    deleteResource(resource) {
      this.$emit('delete', [resource])
    },

    /**
     * Restore the given resource.
     */
    restoreResource(resource) {
      this.$emit('restore', [resource])
    },

    /**
     * Broadcast that the ordering should be updated.
     */
    requestOrderByChange(field) {
      this.$emit('order', field)
    },

    /**
     * Broadcast that the ordering should be reset.
     */
    resetOrderBy(field) {
      this.$emit('reset-order-by', field)
    },

    getQueryParameter(type) {
        return this.viaRelationship
            ? this.viaRelationship + '_' + type
            : this.resourceName + '_' + type
    },

    putFiledCalculate(field) {
        this.calculate.push(field);
    },

    fetchCalculate() {

        let queryStringData = this.$store.getters['queryStringParams'];

        let fields = {
            search: queryStringData[this.getQueryParameter('search')] || '',
            filters: queryStringData[this.getQueryParameter('filter')] || '',
            calculate: this.calculate
        };

        Nova.request()
        .get(`/nova-vendor/nova-totals-footer/calculate/${this.resourceName}`, {
            params: fields,
        })
        .then(response => {
            if (response.data) {
                this.footerSettings = response.data.settings;

                this.calculate.forEach(field => {
                    if (response.data.totals[field.indexName]) {
                        this.calculate.find(o=>o.indexName===field.indexName).value = response.data.totals[field.indexName];
                    }
                });
            }
        })
        .catch(error => {
            throw new Error(error.response.data.message);
        });
    },
  },

    async created(){
        Nova.$on('resources-loaded', this.fetchCalculate)
        if(this.resources.length>0 && this.resources[0].fields){
            this.calculate = [];
            this.resources[0].fields.forEach(field => {
                if (field.calculate_method) {
                    this.putFiledCalculate({
                        indexName: field.sortableUriKey,
                        method: field.calculate_method,
                        value: 0,
                    });
                }
            });
            this.fetchCalculate();
        }
    },

  computed: {
    /**
     * Get all of the available fields for the resources.
     */
    fields() {
      if (this.resources) {
        return this.resources[0].fields
      }
    },

    /**
     * Determine if the current resource listing is via a many-to-many relationship.
     */
    viaManyToMany() {
      return (
        this.relationshipType == 'belongsToMany' ||
        this.relationshipType == 'morphToMany'
      )
    },

    /**
     * Determine if the resource table should show column borders.
     */
    shouldShowColumnBorders() {
      return this.resourceInformation.showColumnBorders
    },

    tableStyle() {
      return this.resourceInformation.tableStyle
    },

    clickAction() {
      return this.resourceInformation.clickAction
    },
  },
}
</script>
