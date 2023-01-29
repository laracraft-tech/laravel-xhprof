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
                profDataSort: 'wt',
                profDataSortDir: 'desc',
                profDataKey2Title: {
                    "symbol": {"partOne": "Function", "partTwo": "Call"},
                    "ct": {"partOne": "Call", "partTwo": "Count"},
                    "wt": {"partOne": "Incl. Wall", "partTwo": "Time"},
                    "cpu": {"partOne": "Incl.", "partTwo": "CPU"},
                    "mu": {"partOne": "Incl.", "partTwo": "Memory"},
                    "pmu": {"partOne": "Incl. Peak", "partTwo": "Memory"},
                    "excl_wt": {"partOne": "Excl. Wall", "partTwo": "Time"},
                    "excl_cpu": {"partOne": "Excl.", "partTwo": "CPU"},
                    "excl_mu": {"partOne": "Excl.", "partTwo": "Memory"},
                    "excl_pmu": {"partOne": "Excl. Peak", "partTwo": "Memory"},
                }
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
                    this.profDataSortDir = this.profDataSortDir === 'asc' ? 'desc':'asc';
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

                    if (this.profDataSort === 'symbol') {
                        return a[1][this.profDataSort].localeCompare(b[1][this.profDataSort]) * -modifier;
                    } else {
                        if(a[1][this.profDataSort] < b[1][this.profDataSort]) return -1 * modifier;
                        if(a[1][this.profDataSort] > b[1][this.profDataSort]) return 1 * modifier;
                        return 0;
                    }
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
                    <th v-for="(title, key) in profDataKey2Title" @click="sort(key)" :key="key">
                        {{ title.partOne }}<br />
                        <span class="iconConnector">
                            {{ title.partTwo }}
                            <span v-if="profDataSort === key">
                                <span v-if="profDataSortDir === 'desc'">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <span v-else>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                        <path fill-rule="evenodd" d="M14.77 12.79a.75.75 0 01-1.06-.02L10 8.832 6.29 12.77a.75.75 0 11-1.08-1.04l4.25-4.5a.75.75 0 011.08 0l4.25 4.5a.75.75 0 01-.02 1.06z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </span>
                            <span v-else>
                                <svg style="color:gray;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    </th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="data in sortedProfData">
                    <td v-for="(title, key) in profDataKey2Title" :key="key" :style="key === 'symbol' ? 'max-width:500px;' : ''">
                        {{data[key]}}
                    </td>
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
