<template>

<container>
    <search-and-option-bar-observer/>
    <search-and-option-bar :isStickable="true">
        <barcode-input-field @barcodeScanned="searchForProductSku" url_param_name="filter[order_number]" ref="barcode" placeholder="Search"/>
        <template v-slot:buttons>
            <top-nav-button v-b-modal="'quick-actions-modal'"/>
        </template>
    </search-and-option-bar>

    <report-head :report-name="breadcrumbs" :auto-expand-filters="records.length === 0"></report-head>

    <div v-if="records === null || records.length === 0" class="text-secondary small text-center pt-2">
        No records found with filters specified
    </div>

    <template v-else>
        <card>
            <!--
            style="transform: rotateX(180deg);"
            this is used twice to move scrollbar to the top of the table
            -->
            <div class="table-responsive py-2" style="transform: rotateX(180deg);">
                <table class="table-hover w-100 text-left small text-nowrap" style="transform: rotateX(180deg); height: 150px">
                    <thead>
                    <tr>
                        <template v-for="field in visibleFields">
                            <th class="small pr-2" v-if="field">
                                <div class="dropdown">
                                    <button class="btn btn-link dropdown-toggle" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ field['display_name'] }}
                                        <font-awesome-icon v-if="isUrlSortedBy(field['name'])" :icon="isUrlSortDesc ? 'caret-down' : 'caret-up'" class="fa-xs" role="button"></font-awesome-icon>
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
                                        <button class="dropdown-item" type="button" @click="setUrlParameterAngGo('sort',  ['-', field.name].join(''))">
                                            <icon-sort-desc class="mr-1"/> Sort Descending
                                        </button>
                                        <button class="dropdown-item" type="button" @click="setUrlParameterAngGo('sort', field.name)">
                                            <icon-sort-asc class="mr-1"/> Sort Ascending
                                        </button>
                                        <button class="dropdown-item" type="button" @click="showFilterModal(field)">
                                            <icon-filter class="mr-1"/> Filter by value
                                        </button>
                                        <button class="dropdown-item" type="button" @click="showShowHideColumnsModal">
                                            <icon-filter class="mr-1"/> Show / Hide Columns
                                        </button>
                                    </div>
                                </div>
                            </th>
                        </template>
                    </tr>
                    </thead>
                    <tbody>
                        <tr class="table-hover align-text-top" v-for="record in records">
                            <template v-for="field in visibleFields" v-if="field">
                                <td v-if="field.type === 'datetime'" class="pr-3">{{ formatDateTime(record[field.name], 'YYYY MMM D HH:mm') }}</td>
                                <td v-else-if="field.type === 'date'" class="pr-3">{{ formatDateTime(record[field.name], 'YYYY MMM D') }}</td>
                                <td v-else-if="field.type === 'numeric'"class="pr-3 text-right">{{ record[field.name] }}</td>
                                <td v-else class="pr-3" >
                                    <template v-if="field.name === 'order_numbers'">
                                        <product-sku-button :order_number="record[field.name]"/>
                                    </template>
                                    <template v-else>
                                        {{ record[field.name] }}
                                    </template>
                                </td>
                            </template>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="!hasMoreRecords" class="text-secondary small text-center mt-3">
                No more records found.
            </div>
        </card>
        <div class="row">
            <div class="col">
                <div ref="loadingContainerOverride" style="height: 32px"></div>
            </div>
        </div>
    </template>

    <b-modal id="filter-box-modal" size="sm" no-fade hide-header @shown="focusFilterBoxInput">
        <div v-if="filterAdding" class="d-flex flex-column" style="gap: 5px;">
            <select v-model="filterAdding.selectedOperator" @change="focusFilterBoxInput" class="form-control form-control-sm">
                <option v-for="operator in filterAdding.operators" :key="operator" :value="operator">{{ operator === 'btwn' ? 'between' : operator }}</option>
            </select>
            <form @submit.prevent="addFilter" @keyup.enter="addFilter" class="d-flex flex-row" style="grid-gap: 5px;">
                <!-- between filter inputs -->
                <template v-if="filterAdding.selectedOperator === 'btwn'">
                    <input v-model="filterAdding.value" id='inputFilterBetweenValueFrom' :type="filterAdding.selectedField.type === 'numeric' ? 'number' : 'text'" class="form-control form-control-sm">
                    <input v-model="filterAdding.valueBetween" id='inputFilterBetweenValueTo' :type="filterAdding.selectedField.type === 'numeric' ? 'number' : 'text'" class="form-control form-control-sm">
                </template>

                <!-- other filters -->
                <template v-else>
                  <input v-model="filterAdding.value" id='inputFilterValue' :type="filterAdding.selectedField.type === 'numeric' ? 'number' : 'text'" class="form-control form-control-sm">
                </template>
            </form>
        </div>
        <template #modal-footer>
            <b-button variant="secondary" class="float-right" @click="$bvModal.hide('filter-box-modal')">Cancel</b-button>
            <b-button variant="primary" class="float-right" @click="addFilter">Apply</b-button>
        </template>
    </b-modal>

    <ModalDateBetweenSelector
        :starting_date.sync="filterAdding.value"
        :ending_date.sync="filterAdding.valueBetween"
        @close="$bvModal.hide('modal-date-selector-widget')"
        @apply="addFilter"
    />

    <b-modal id="quick-actions-modal" no-fade hide-header @hidden="setFocusElementById('barcode-input')">
        <stocktake-input v-bind:auto-focus-after="100" ></stocktake-input>
        <hr>
        <button class="btn btn-primary btn-block" @click="downloadFile">{{ downloadButtonText }}</button>
        <template #modal-footer>
            <b-button variant="secondary" class="float-right" @click="$bvModal.hide('quick-actions-modal');">
                Cancel
            </b-button>
            <b-button variant="primary" class="float-right" @click="$bvModal.hide('quick-actions-modal');">
                OK
            </b-button>
        </template>
    </b-modal>

    <b-modal id="show-hide-columns-local-modal" no-fade header-class="small" @show="showSelection" @hidden="setFocusElementById('barcode-input')">
        <template #modal-header>Show \ Hide Columns</template>
        <b-form-group>
            <b-form-checkbox v-for="option in fields" v-if="option" v-model="selected" :key="option.name" :value="option.name"> {{ option.display_name }}</b-form-checkbox>
        </b-form-group>

        <template #modal-footer>
            <b-button variant="secondary" class="float-right" @click="$bvModal.hide('show-hide-columns-local-modal');">Cancel</b-button>
            <b-button variant="primary" class="float-right" @click="updateVisibleFieldsAndGo">OK</b-button>
        </template>
    </b-modal>

</container>

</template>

<script>
    import loadingOverlay from '../../mixins/loading-overlay';
    import url from "../../mixins/url";
    import api from "../../mixins/api";
    import IconSortAsc from "../UI/Icons/IconSortAsc.vue";
    import IconSortDesc from "../UI/Icons/IconSortDesc.vue";
    import IconFilter from "../UI/Icons/IconFilter.vue";
    import IconArrowRight from "../UI/Icons/IconArrowRight.vue";
    import IconArrowLeft from "../UI/Icons/IconArrowLeft.vue";
    import ModalDateBetweenSelector from "../Widgets/ModalDateBetweenSelector.vue";
    import SearchFilter from "./SearchFilter.vue";
    import ReportHead from "./ReportHead.vue";
    import moment from "moment";
    import helpers from "../../helpers";
    import ProductSkuButton from "../SharedComponents/ProductSkuButton.vue";

    export default {
        mixins: [loadingOverlay, url, api, helpers],

        components: {
            ProductSkuButton,
            IconArrowRight, IconArrowLeft, IconSortAsc, IconSortDesc, IconFilter, ModalDateBetweenSelector, SearchFilter, ReportHead},

        props: {
            metaString: String,
            recordString: String,
            downloadButtonText: String,
        },

        data() {
            return {
                meta: JSON.parse(this.metaString),
                records: JSON.parse(this.recordString),
                fields: JSON.parse(this.metaString)['field_links'],
                selected: [], // Must be an array reference!
                filters: [],
                filterAdding: null,
                showFilters: true,
                perPage: 50,
                page: 1,
                hasMoreRecords: true,

                breadcrumbs: '',
            }
        },

        beforeMount() {
            this.setFilterAdding()
        },

        mounted() {
            this.perPage = this.getUrlParameter('per_page', 50);

            this.buildFiltersFromUrl();
            window.onscroll = () => this.loadMoreRecords();

            this.breadcrumbs = this.$router.currentRoute.path
                .replace('/', '')
                .replaceAll('/', ' > ')
                .replaceAll('-', ' ');
        },

        methods: {
            downloadFile() {
                let filename  = this.meta['report_name']+'.csv';
                let url       = this.$router.currentRoute;
                let urlParams = new URLSearchParams(window.location.search);

                urlParams.set('filename', filename);

                helpers.downloadFile(url.path + '?' + urlParams.toString(), filename);
            },

            searchForProductSku(barcode) {
                if (barcode === '') {
                    this.removeUrlParameterAndGo('filter[order_number]');
                    return;
                }

                this.setUrlParameterAngGo('filter[order_number]', barcode);
            },

            focusFilterBoxInput() {
                switch (this.filterAdding.selectedOperator) {
                    case 'btwn':
                        this.setFocusElementById('inputFilterBetweenValueFrom', true)
                        break;
                    default:
                        this.setFocusElementById('inputFilterValue', true)
                        break;
                }
            },

            showFilterModal(field){
                if(['date', 'datetime'].includes(field.type)) {
                    this.$bvModal.show('modal-date-selector-widget')
                    this.setFilterAdding(field.name);
                    return;
                }

                this.$bvModal.show('filter-box-modal')
                this.setFilterAdding(field.name);
            },

            setFilterAdding(fieldName = null) {
                let selectedField = fieldName ? this.fields.find(f => f.name === fieldName) : this.fields[0];
                let existingFilter = this.filters.find(f => f.name === selectedField.name);
                let defaultOperator = selectedField.operators[0];
                let defaultFilterValueMin = '';
                let defaultFilterValueMax = '';

                switch (selectedField.type) {
                    case 'numeric':
                        defaultOperator = 'btwn';
                        defaultFilterValueMin = 0;
                        defaultFilterValueMax = 0;
                        break;
                    case 'string':
                        defaultOperator = 'equals';
                        defaultFilterValueMin = '';
                        defaultFilterValueMax = '';
                        break;
                    case 'date':
                        defaultOperator = 'btwn';
                        defaultFilterValueMin = moment().startOf("year").format('YYYY-MM-DD HH:mm');
                        defaultFilterValueMax = moment().endOf("day").format('YYYY-MM-DD HH:mm');
                        break;
                    case 'datetime':
                        defaultOperator = 'btwn';
                        defaultFilterValueMin = moment().startOf("day").format('YYYY-MM-DD HH:mm');
                        defaultFilterValueMax = moment().endOf("day").format('YYYY-MM-DD HH:mm');
                        break;
                    default:
                        defaultOperator = 'contains';
                        defaultFilterValueMin = '';
                        defaultFilterValueMax = '';
                }

                let selectedOperator = existingFilter ? existingFilter.selectedOperator : defaultOperator;
                let filterValueMin = existingFilter ? existingFilter.value : defaultFilterValueMin;
                let filterValueMax = existingFilter ? existingFilter.valueBetween : defaultFilterValueMax;

                this.filterAdding = {
                    fields: this.fields,
                    selectedField: selectedField,
                    operators: selectedField.operators,
                    selectedOperator: selectedOperator,
                    value: filterValueMin,
                    valueBetween: filterValueMax,
                }
            },

            buildFiltersFromUrl() {
                const urlParams = new URLSearchParams(window.location.search);

                for (const [key, value] of urlParams.entries()) {
                    if(key.startsWith('filter')) {
                        let filterName = key.split('[')[1].split(']')[0];

                        let fieldName = filterName;
                        fieldName = fieldName.replaceAll('_equal','');
                        fieldName = fieldName.replaceAll('_contains','');
                        fieldName = fieldName.replaceAll('_between','');
                        fieldName = fieldName.replaceAll('_lower_than','');
                        fieldName = fieldName.replaceAll('_greater_than','');
                        fieldName = fieldName.replaceAll('_starts_with','');

                        let filterOperator = filterName.replace(fieldName, '');
                        let filterOperatorHumanString = filterOperator;

                        let field = this.findField(fieldName);

                        switch (filterOperator) {
                          case '':
                            filterOperatorHumanString = 'equals'
                            break;
                          case '_equal':
                            filterOperatorHumanString = 'equals'
                            break;
                          case '_contains':
                            filterOperatorHumanString = 'contains'
                            break;
                          case '_between':
                            filterOperatorHumanString = 'between'
                            break;
                          case '_greater_than':
                            filterOperatorHumanString = 'greater than'
                            break;
                          case '_lower_than':
                            filterOperatorHumanString = 'lower than'
                          case '_starts_with':
                            filterOperatorHumanString = 'starts with'
                            break;
                          default:
                            filterOperatorHumanString = filterOperator
                        }

                        let filter = {
                            name: fieldName,
                            displayName: '', //field.display_name,
                            selectedOperator: filterOperator === '_between' ? 'btwn' : filterOperatorHumanString,
                            value: value,
                            valueBetween: '',
                        }

                        if(filterOperator === '_between') {
                            let values = Array.isArray(value) ? value : value.split(',');
                            filter.value = values[0];
                            filter.valueBetween = values[1];
                        }

                        this.filters.push(filter);
                    }
                }
            },

            addFilter() {
                const { value, selectedOperator, valueBetween, selectedField } = this.filterAdding;

                let filterName = '';
                let filterValue = '';

                switch (selectedOperator) {
                  case 'equals':
                    filterName = ['filter[', selectedField.name, ']'].join('');
                    filterValue = `${value}`;
                    break;
                  case 'btwn':
                    filterName = ['filter[', selectedField.name, '_between]'].join('');
                    filterValue = `${value},${valueBetween}`;
                    break;
                  case 'greater than':
                    filterName = `filter[${selectedField.name}_greater_than]`;
                    filterValue = value;
                    break;
                  case 'lower than':
                    filterName = `filter[${selectedField.name}_lower_than]`;
                    filterValue = value;
                    break;
                  case 'starts with':
                    filterName = `filter[${selectedField.name}_starts_with]`;
                    filterValue = value;
                    break;
                  default:
                    filterName = `filter[${selectedField.name}_${selectedOperator}]`;
                    filterValue = value;
                }

                this.setUrlParameterAngGo(filterName, filterValue)
            },

            findField(fieldName) {
                return this.fields.find(f => f.name === fieldName);
            },

            loadMoreRecords(){
                if (helpers.isMoreThanPercentageScrolled(70) && this.hasMoreRecords && !this.isLoading) {
                    this.showLoading();

                    this.page++;

                    let urlParams = new URLSearchParams(window.location.search);
                    urlParams.set('filename', 'data.json');
                    urlParams.set('page', this.page);
                    urlParams.set('per_page', this.perPage);

                    this.getReportData(this.$router.currentRoute.path, urlParams)
                        .then(response => {
                            this.records = this.records.concat(response.data.data);
                            this.hasMoreRecords = response.data.data.length === this.perPage;
                            this.isLoading = false;
                        })
                        .catch(error => {
                            this.displayApiCallError(error);
                        })
                        .finally(() => {
                            this.hideLoading();
                        });
                }
            },

            getReportData: function(url, params) {
                return axios.get(url, {params: params})
            },

            isUrlSortedBy(field) {
                return this.getUrlParameter('sort', '').includes(field);
            },

            showSelection() {
                this.selected = this.visibleFields.map(f => f?.name);
            },

            updateVisibleFieldsAndGo() {
                this.$bvModal.hide('show-hide-columns-local-modal');
                this.setUrlParameterAngGo('select', this.selected.join(','));
            },

            showShowHideColumnsModal() {
                this.$bvModal.show('show-hide-columns-local-modal');
            }
        },

        computed: {
            helpers() {
                return helpers
            },

            visibleFields() {
                return Object.keys(this.records[0])
                    .map(this.findField);
            },

            isUrlSortDesc() {
                return this.getUrlParameter('sort', ' ').startsWith('-');
            }
        }
    }

    </script>

<style scoped>

.dropdown > .btn.dropdown-toggle {
    font-size: 12px;
    padding: 0;
    color: black;
    font-weight: bold;
    &:focus, &:active {
        outline: none;
        box-shadow: none;
        border-color: transparent;
    }
}

.dropdown-toggle::after {
    display: none;
}

::-webkit-scrollbar{
    height: 4px;
    width: 4px;
}

::-webkit-scrollbar-thumb:horizontal{
    background: lightgray;
    border-radius: 10px;
}

::-webkit-scrollbar:horizontal{
    height: 8px;
    background: none;
}

</style>
