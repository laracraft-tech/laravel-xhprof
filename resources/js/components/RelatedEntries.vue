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
                // currentTab: 'exceptions'
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


        methods: {
            // activateTab(tab){
            //     this.currentTab = tab;
            //     if(window.history.replaceState) {
            //         window.history.replaceState(null, null, '#' + this.currentTab);
            //     }
            // }
        },


        computed: {
            profData() {
                //fore testing -> faster rendering...
                // const slicedArray = Object.entries(this.entry.profData).slice(0, 10);
                // return Object.fromEntries(slicedArray);
                return this.entry.profData;
            },
        }
    }
</script>

<template>
    <div class="card mt-5" v-if="profData">
        <div>
            <!-- Related Queries -->
            <table class="table table-hover table-sm mb-0">
                <thead>
                <tr>
                    <th>Function</th>
                    <th>Call Count</th>
                    <th>Wall Time</th>
                    <th>CPU</th>
                    <th>Memory Usage</th>
                    <th>Peak Memory Usage</th>
                    <th>Exclusive Wall Time</th>
                    <th>Exclusive CPU</th>
                    <th>Exclusive Memory Usage</th>
                    <th>Exclusive Peak Memory Usage</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(data, func) in profData">
                    <td :title="func" class="table-fit">{{truncate(func, 50)}}</td>
                    <td>{{data.ct}}</td>
                    <td>{{data.wt}}</td>
                    <td>{{data.cpu}}</td>
                    <td>{{data.mu}}</td>
                    <td>{{data.pmu}}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>

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
