<script type="text/ecmascript-6">
    import StylesMixin from './../mixins/entriesStyles';


    export default {
        props: ['entry'],


        mixins: [
            StylesMixin,
        ],


        /**
         * The component's data.
         */
        data(){
            return {
                profData: this.entry.profData,
                profDataSort:'wt',
                profDataSortDir:'desc'
            };
        },


        /**
         * Prepare the component.
         */
        mounted(){
            // this.activateFirstTab();
            // console.log(this.entry.profData);
        },


        // watch: {
        //     entry(){
        //         this.activateFirstTab();
        //     }
        // },

        methods:{
            sort:function(s) {
                //if s == current sort, reverse
                if(s === this.profDataSort) {
                    this.profDataSortDir = this.profDataSortDir==='asc' ? 'desc':'asc';
                }

                this.profDataSort = s;
            }
        },

        computed:{
            sortedProfData:function() {
                // const slicedArray = Object.entries(this.profData).slice(0, 10);
                // this.profData = Object.fromEntries(slicedArray);

                return Object.fromEntries(Object.entries(this.profData).sort((a,b) => {
                    let modifier = 1;
                    if(this.profDataSortDir === 'desc') modifier = -1;
                    if(a[1][this.profDataSort] < b[1][this.profDataSort]) return -1 * modifier;
                    if(a[1][this.profDataSort] > b[1][this.profDataSort]) return 1 * modifier;
                    return 0;
                }));
            }
        }
    }
</script>

<template>
    <div class="prof-data-list card mt-5" v-if="sortedProfData">
        <div>
            <!-- Related Queries -->
            <table class="table table-hover table-sm mb-0">
                <thead>
                <tr>
                    <th style="cursor:auto;">Function</th>
                    <th class="pointer" @click="sort('ct')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                        Call Count
                    </th>
                    <th class="pointer" @click="sort('wt')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                        Wall Time
                    </th>
                    <th class="pointer" @click="sort('cpu')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                        CPU
                    </th>
                    <th class="pointer" @click="sort('mu')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                        Memory
                    </th>
                    <th class="pointer" @click="sort('pmu')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                        Peak Memory
                    </th>
                    <th class="pointer" @click="sort('ct')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                        Exclusive Wall Time
                    </th>
                    <th class="pointer" @click="sort('ct')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                        Exclusive CPU
                    </th>
                    <th class="pointer" @click="sort('ct')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                        Exclusive Memory
                    </th>
                    <th class="pointer" @click="sort('ct')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                        Exclusive Peak Memory
                    </th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(data, func) in sortedProfData">
                    <td :title="func">{{func}}</td>
                    <td>{{data.ct}}</td>
                    <td>{{data.wt}}</td>
                    <td>{{data.cpu}}</td>
                    <td>{{data.mu}}</td>
                    <td>{{data.pmu}}</td>
                    <td>{{data.excl_wt}}</td>
                    <td>{{data.excl_cpu}}</td>
                    <td>{{data.excl_mu}}</td>
                    <td>{{data.excl_pmu}}</td>

                    <td class="table-fit">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 16">
                            <path d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path>
                        </svg>

<!--                        <router-link :to="{name:'query-preview', params:{id: data.id}}" class="control-action">-->
<!--                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 16">-->
<!--                                <path d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path>-->
<!--                            </svg>-->
<!--                        </router-link>-->
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
    td {
        vertical-align: middle !important;
    }
</style>
