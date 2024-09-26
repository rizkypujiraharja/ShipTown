<template>
  <div class="card mb-2">
    <div class="row card-body p-1 p-md-2 h-100">
        <div class="col-12 col-lg-4 align-text-top">
            <product-info-card :product= "record['product']"></product-info-card>
        </div>

        <div class="row col-sm-12 col-lg-8 text-right mt-1">
            <div class="col-12 col-md-6">
                <table class="table-borderless small text-left text-nowrap">
                  <tr>
                    <td @click="toggleDetails()" style="min-width: 120px">occurred at:</td>
                    <td class="pl-1">{{ formatDateTime(record['occurred_at']) }}</td>
                  </tr>
                  <tr>
                    <td @click="toggleDetails()">type:</td>
                    <td class="pl-1" v-bind:class="{ 'bg-warning': record['type'] === 'stocktake' }"><a :href="getLinkInvetoryMovementsReport('filter[type]', record['type'])">{{ record['type'] }}</a></td>
                  </tr>
                  <tr>
                    <td @click="toggleDetails()">description:</td>
                    <td class="pl-1"><a :href="getLinkInvetoryMovementsReport('filter[description]', record['description'])">{{ record['description'] }}</a></td>
                  </tr>
                  <tr>
                    <td @click="toggleDetails()">by:</td>
                    <td class="pl-1">{{ record['user'] ? record['user']['name'] : '' }}</td>
                  </tr>
                    <template v-if="showDetails" @click="toggleDetails()">
                        <tr>
                            <td @click="toggleDetails()">movement id:</td>
                            <td class="pl-1">{{ record['id'] }}</td>
                        </tr>
                        <tr>
                            <td @click="toggleDetails()">sequence number:</td>
                            <td class="pl-1">{{ record['sequence_number'] }}</td>
                        </tr>
                        <tr>
                            <td @click="toggleDetails()">uuid:</td>
                            <td class="pl-1">{{ record['custom_unique_reference_id'] }}</td>
                        </tr>
                        <tr>
                            <td @click="toggleDetails()">shelf:</td>
                            <td class="pl-1">{{ record['inventory']['shelf_location'] }}</td>
                        </tr>
                        <tr>
                            <td @click="toggleDetails()">in stock:</td>
                            <td class="pl-1">{{ dashIfZero(Number(record['inventory']['quantity'])) }}</td>
                        </tr>
                    </template>
                </table>
            </div>
            <div class="col-12 col-md-6 text-right align-text-top h-100" @click="toggleDetails()">
                <text-card label="warehouse" :text="record['inventory']['warehouse_code']" class="fa-pull-left"></text-card>
                <number-card label="before" :number="record['quantity_before']"></number-card>
                <number-card label="change" :number="record['quantity_delta']"></number-card>
                <number-card label="after" :number="record['quantity_after']"></number-card>
                <div class="row text-center text-secondary">
                    <div class="col">
                        <font-awesome-icon v-if="showDetails" icon="chevron-up" class="fa fa-xs"></font-awesome-icon>
                        <font-awesome-icon v-if="!showDetails" icon="chevron-down" class="fa fa-xs"></font-awesome-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>


<script>

import helpers from "./../../mixins/helpers";
import url from "../../mixins/url";

export default {
    mixins: [helpers, url],
    props: {
        record: {
            type: Object,
            required: true
        }
    },
    data: function() {
        return {
            showDetails: false
        };
    },

    methods: {
        toggleDetails: function() {
            this.showDetails = !this.showDetails;
        },
        getLinkInvetoryMovementsReport(param, value) {
            return `/reports/inventory-movements?${param}=${value}`;
        }
    }
}
</script>
