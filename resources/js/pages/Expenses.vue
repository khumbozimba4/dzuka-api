<template>
    <div class="Main__Wrapper">
        <ConfirmDelete
            @toggleCancel="toggleCancel"
            @toggleDelete="toggleDelete"
            v-if="confirmDelete"
        />
        <div class="NavBar__Container">
            <div class="Title">
                <CreditCardIcon class="Icon" />
                <p>Expenses</p>
            </div>
            <div class="Search__Bar">
                <input
                    type="text"
                    class="Input"
                    placeholder="Search.."
                    v-model="search"
                />
                <SearchIcon class="Search__Icon" />
            </div>

            <div class="Options"></div>
        </div>
        <div v-if="errorMessage">{{ errorMessage }}</div>
        <ExpenseSearch :search="search" v-if="search" />
        <div class="Contents__Container">
            <div class="Heading">
                <div class="Left__Side">
                    <AdjustmentsIcon class="Icon" />
                    Filters
                </div>
                <div class="Right__Side">
                    <div
                        class="Add__Category"
                        @click="isOpen = !isOpen"
                        v-if="userInfo.role !== 'Finance'"
                    >
                        Record Expense
                    </div>
                    <PrinterIcon class="Icon" />
                </div>
                <AddExpense
                    @getExpenses="getExpenses"
                    v-if="isOpen"
                    @closeModal="isOpen = !isOpen"
                />
            </div>
            <div class="Table__Container">
                <table class="Table">
                    <thead class="Table__Head">
                        <tr class="Tr">
                            <td>#</td>
                            <td>Date</td>
                            <td>Expense on</td>
                            <td>Amount (MWK)</td>
                            <td>Description</td>
                            <td v-if="userInfo.role !== 'Finance' && userInfo.role !== 'Operations'">Actions</td>
                        </tr>
                    </thead>
                    <tbody class="Table__Body">
                        <tr
                            class="Tr"
                            v-for="(expense, index) in list"
                            :key="expense.id"
                        >
                            <td>
                                <strong>{{ index + 1 }}</strong>
                            </td>
                            <td>{{ getDate(expense.date) }}</td>
                            <td>{{ expense.expense_on }}</td>
                            <td>{{ getCurrency(expense.amount) }}</td>
                            <td>{{ expense.description ? expense.description : "N/A" }}</td>
                            <td
                                class="Icons"
                                v-if="userInfo.role !== 'Finance' && userInfo.role !== 'Operations'"
                            >
                                <PencilIcon
                                    class="Icon"
                                    @click="toggleEditExpense(expense)"
                                />
                                <TrashIcon
                                    class="Icon Icon_Delete"
                                    @click="toggleDeleteExpense(expense.id)"
                                    :style="deleteIcon"
                                />
                                <EditExpense
                                    :expense="expense"
                                    @getExpenses="getExpenses"
                                    v-if="
                                        editExpenseOpen &&
                                        selected === list[index]
                                    "
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <TablePagination
                    v-if="list.length"
                    :current="expenses.current_page"
                    :total="expenses.total"
                    :per_page="expenses.per_page"
                    :prev_page_url="expenses.prev_page_url"
                    :next_page_url="expenses.next_page_url"
                    @change="(value) => getExpenses(value)"
                />
                <div class="p-4" v-if="!list.length">
                    No expenses incurred yet
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
    TrashIcon,
} from "@heroicons/vue/outline";
import AddExpense from "../components/expenses/AddExpense.vue";
import EditExpense from "../components/expenses/EditExpense.vue";
import ConfirmDelete from "../components/ConfirmDelete.vue";
import axios from "axios";
import { mapActions, mapGetters } from "vuex";
import { CurrencyFormatter } from "../factories/CurrencyFormatterFactory";
import moment from "moment";
import { API } from "../api";
import TablePagination from "../components/TablePagination.vue";

export default {
    components: {
        AddExpense,
        EditExpense,
        ConfirmDelete,
        SearchIcon,
        PencilIcon,
        CollectionIcon,
        AdjustmentsIcon,
        PrinterIcon,
        ArrowNarrowRightIcon,
        CreditCardIcon,
        ShoppingBagIcon,
        TrashIcon,
        TablePagination,
    },
    data() {
        return {
            expenses: null,
            list: [],
            isOpen: false,
            errorMessage: null,
            search: "",
            editExpenseOpen: false,
            selected: null,
            deleteIcon: "Icon_Delete",
            confirmDelete: false,
            deletedItem: null,
        };
    },
    computed: {
        ...mapGetters(["userInfo"]),
    },
    created() {
        this.getExpenses();
    },
    methods: {
        ...mapActions(["changeLoading"]),
        getExpenses(page) {
            this.changeLoading();
            API.listExpenses(page)
                .then(({ data }) => {
                    this.expenses = data;
                    this.list = data.data;
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.changeLoading();
                    this.errorMessage = err.message;
                });
        },
        toggleEditExpense(expense) {
            this.selected = expense;
            this.editExpenseOpen = !this.editExpenseOpen;
        },
        toggleDeleteExpense(id) {
            this.deletedItem = id;
            this.confirmDelete = true;
        },
        toggleDelete() {
            this.changeLoading();
            API.deleteExpense(this.deletedItem)
                .then(() => {
                    this.confirmDelete = false;
                })
                .then(() => {
                    this.getExpenses();
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.errorMessage = err.message;
                });
        },
        toggleCancel() {
            this.confirmDelete = false;
        },
        getCurrency(amount) {
            return CurrencyFormatter.getCurrency(amount);
        },
        getDate(date) {
            return moment(new Date(date)).format("LL");
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
