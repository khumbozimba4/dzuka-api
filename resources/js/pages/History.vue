<template>
    <div class="Main__Wrapper">
        <div class="NavBar__Container">
            <div class="Title">
                <CollectionIcon class="Icon" />
                <p>
                    <strong>{{ this.$route.params.product_name }}</strong> |
                    Stock History
                </p>
            </div>
            <div class="Search__Bar">
                <input type="text" class="Input" placeholder="Search product" />
                <SearchIcon class="Search__Icon" />
            </div>
            <div class="Options"></div>
        </div>

        <div class="Contents__Container">
            <div class="Heading">
                <div class="Left__Side">
                    <AdjustmentsIcon class="Icon" />
                    Filters
                </div>
            </div>

            <!--            Tabs-->

            <div class="Table__Container">
                <table class="Table" v-if="active_tab === 1">
                    <thead class="Table__Head">
                        <tr class="Tr">
                            <td>Date</td>
                            <td>Stock Counted</td>
                            <td>Recorded By</td>
                        </tr>
                    </thead>
                    <tbody class="Table__Body">
                        <tr
                            class="Tr"
                            v-for="submit in submitAuditStocks"
                            :key="submit.id"
                        >
                            <td>{{ '2' }}</td>
                            <td>{{ submit.stock_count }}</td>
                            <td>{{ submit.submitted_by }}</td>
                        </tr>
                    </tbody>
                </table>

                <table class="Table" v-if="active_tab === 2">
                    <thead class="Table__Head">
                        <tr class="Tr">
                            <td>Date</td>
                            <td>Quantity Submitted</td>
                            <td>Supplier</td>
                        </tr>
                    </thead>
                    <tbody class="Table__Body">
                        <tr
                            class="Tr"
                            v-for="add in addInventories"
                            :key="add.id"
                        >
                            <td>{{'2' }}</td>
                            <td>{{ add.quantity }}</td>
                            <td>{{ Sam }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import {
    CollectionIcon,
    AdjustmentsIcon,
    SearchIcon,
    PrinterIcon,
    ArrowNarrowRightIcon,
    TrashIcon,
} from "@heroicons/vue/outline";
import AddProduct from "../components/AddProduct.vue";
import ConfirmDelete from "../components/ConfirmDelete.vue";
import axios from "axios";
import { mapActions, mapGetters } from "vuex";
import moment from "moment";
export default {
    components: {
        AddProduct,
        ConfirmDelete,
        SearchIcon,
        CollectionIcon,
        AdjustmentsIcon,
        ArrowNarrowRightIcon,
        PrinterIcon,
        TrashIcon,
    },
    data() {
        return {
            errMessage: "",
            active_tab: 1,
            addInventories: null,
            submitAuditStocks: null,
        };
    },
    computed: {
        ...mapGetters(["userInfo"]),
    },
    created() {
        this.getAddInventories();
        this.getSubmitAuditStock();
    },
    methods: {
        ...mapActions(["changeLoading"]),
        getDate(date) {
            return moment(new Date(date)).format("LL");
        },
        getAddInventories() {
            axios
                .get("api/add-inventory")
                .then((res) => {
                    this.addInventories = res?.data;
                    console.log(res.data);
                })
                .catch((err) => {
                    console.log(err.message);
                });
        },
        getSubmitAuditStock() {
            axios
                .get("api/submit-audit-stock")
                .then((res) => {
                    this.submitAuditStocks = res?.data;
                    console.log(res.data);
                })
                .catch((err) => {
                    console.log(err.message);
                });
        },
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrapper {
    width: 100%;
    display: flex;
    flex-direction: column;
    .Tabs {
        border-bottom: 1px solid black;
        display: flex;
        .active_tab {
            background-color: lightseagreen;
            color: white;
        }
        .tab {
            flex: 1;
            padding: 10px;
            font-weight: bold;
            border-left: 1px solid black;
            cursor: pointer;
            text-align: center;
        }
    }
    .NavBar__Container {
        background-color: #fff;
        display: flex;
        align-items: center;
        width: 100%;
        padding: 10px;

        .Title {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 0 10px;
            border-right: 1px solid gray;
            margin-right: 25px;
            .Icon {
                height: 30px;
                object-fit: contain;
            }
        }

        .Search__Bar {
            display: flex;
            align-items: center;
            background-color: rgb(212 212 212);
            border-radius: 5px;
            .Input {
                background: none;
                border: 0px;
                padding: 10px 20px;
                width: 200px;

                &:focus {
                    outline: none;
                    border: 0px;
                }
            }
            .Search__Icon {
                padding: 5px 20px;
                height: 30px;
                color: rgb(115 115 115);
            }
        }
    }
    .Contents__Container {
        margin: 20px;
        background-color: #fff;
        border-radius: 3px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
            0 4px 6px -4px rgb(0 0 0 / 0.1);
        .Heading {
            position: relative;
            display: flex;
            justify-content: space-between;
            padding: 20px;
            border-bottom: 1px solid rgb(163 163 163);
            .Left__Side {
                display: flex;
                gap: 10px;
                align-items: center;
                font-weight: 700;
                cursor: pointer;

                .Icon {
                    height: 20px;
                    object-fit: contain;
                    cursor: pointer;
                }
            }
            .Right__Side {
                display: flex;
                align-items: center;
                gap: 10px;
                .Add__Category {
                    padding: 5px 20px;
                    border: 1px solid rgb(115 115 115);
                    border-radius: 3px;
                    color: rgb(115 115 115);
                    cursor: pointer;
                    font-size: 15px;

                    &:hover {
                        color: rgb(82 82 82);
                    }
                }
                .Icon {
                    height: 30px;
                    object-fit: contain;
                    padding: 5px 20px;
                    border: 1px solid rgb(115 115 115);
                    border-radius: 3px;
                    color: rgb(115 115 115);
                    cursor: pointer;
                }
            }
        }
        .Table__Container {
            padding: 20px;
            .Table {
                width: 100%;

                .Table__Head {
                    font-weight: 800;
                    color: rgb(38 38 38);
                    .Tr {
                        height: 40px;
                    }
                }
                .Table__Body {
                    .Tr {
                        border-top: 1px solid rgb(229 229 229);
                        height: 40px;
                        &:hover {
                            background-color: rgb(236, 236, 236);
                        }
                        .Icons {
                            display: flex;
                            gap: 30px;
                        }
                        td {
                            position: relative;
                            .Icon {
                                width: 25px;
                                object-fit: contain;
                                cursor: pointer;
                                color: rgb(23, 34, 49);
                                &:hover {
                                    color: rgb(2, 2, 3);
                                }
                            }
                            .Icon_Delete {
                                color: rgb(209, 74, 74);
                                &:hover {
                                    color: rgb(155, 23, 23);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
</style>
