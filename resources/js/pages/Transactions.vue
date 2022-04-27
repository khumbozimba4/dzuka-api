<template>
    <div class="Main__Wrapper">
        <div class="NavBar__Container">
            <div class="Title">
                <CreditCardIcon class="Icon" />
                <p>Transactions</p>
            </div>
            <div class="Search__Bar">
                <input
                    type="text"
                    class="Input"
                    placeholder="Search transaction"
                    v-model="search"
                />
                <SearchIcon class="Search__Icon" />
            </div>

            <div class="Options"></div>
        </div>
        <TransactionSearch v-if="search" :search="search" />

        <div v-if="errorMessage">{{ errorMessage }}</div>

        <div class="Contents__Container">
            <div class="Heading">
                <div class="Left__Side">
                    <AdjustmentsIcon class="Icon" />
                    Filters
                </div>
                <div class="Right__Side">
                    <PrinterIcon class="Icon" />
                </div>
            </div>
            <div class="Table__Container">
                <table class="Table">
                    <thead class="Table__Head">
                        <tr class="Tr">
                            <td>ID</td>
                            <td>Date</td>
                            <td>User</td>
                            <td>Transaction Name</td>
                            <td>Description</td>
                        </tr>
                    </thead>
                    <tbody class="Table__Body">
                        <tr
                            class="Tr"
                            v-for="transaction in transactions"
                            :key="transaction.id"
                        >
                            <td>
                                <strong>{{ transaction.id }}</strong>
                            </td>
                            <td>
                                {{ getDate(transaction.created_at) }}
                            </td>
                            <td>{{ transaction.user.name }}</td>
                            <td>{{ transaction.transaction_name }}</td>

                            <td>{{ transaction.description }}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="p-4" v-if="transactions.length == 0">
                    No transactions recorded yet
                </div>
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
    ShoppingBagIcon,
    CreditCardIcon,
    PencilIcon,
} from "@heroicons/vue/outline";
import TransactionSearch from "../components/TransactionSearch.vue";
import moment from "moment";
import axios from "axios";
export default {
    components: {
        TransactionSearch,
        SearchIcon,
        PencilIcon,
        CollectionIcon,
        AdjustmentsIcon,
        PrinterIcon,
        ArrowNarrowRightIcon,
        PrinterIcon,
        CreditCardIcon,
        ShoppingBagIcon,
    },
    data() {
        return {
            transactions: [],
            isOpen: false,
            errorMessage: null,
            search: "",
            editExpenseOpen: false,
            selected: null,
        };
    },
    created() {
        this.getTransactions();
    },
    methods: {
        getDate(date) {
            return moment(date).format("MMM Do YY");
        },
        getTransactions() {
            axios
                .get("api/transactions")
                .then((res) => {
                    this.transactions = res.data;
                })
                .catch((err) => {
                    this.errorMessage = err.message;
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
                padding: 10px 10px;
                width: 180px;

                &:focus {
                    outline: none;
                    border: 0px;
                }
            }
            .Search__Icon {
                padding: 5px 10px;
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
                        }
                    }
                }
            }
        }
    }
}
</style>
