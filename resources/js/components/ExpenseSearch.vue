<template>
    <div class="Contents__Container">
        <div class="Heading">
            <h1>Search Results</h1>
            <h3 v-if="expenses.length == 0">
                Oops we cant find that expense for you😢
            </h3>
        </div>
        <div class="Table__Container" v-if="expenses.length !== 0">
            <table class="Table">
                <thead class="Table__Head">
                    <tr class="Tr">
                        <td>ExpenseID</td>
                        <td>Date</td>
                        <td>Expense on</td>
                        <td>Amount (MKW)</td>
                        <td>Description</td>
                    </tr>
                </thead>
                <tbody class="Table__Body">
                    <tr
                        class="Tr"
                        v-for="expense in expenses"
                        :key="expense.id"
                    >
                        <td>
                            <strong>{{ expense.id }}</strong>
                        </td>
                        <td>{{ expense.date }}</td>
                        <td>{{ expense.expense_on }}</td>
                        <td>{{ expense.amount }}</td>
                        <td>{{ expense.description }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="p-4" v-if="expenses.length == 0">
                No expenses incurred yet
            </div>
        </div>
    </div>
</template>

<script>
import { ArrowNarrowRightIcon } from "@heroicons/vue/outline";
import axios from "axios";
export default {
    props: ["search"],
    components: {
        ArrowNarrowRightIcon,
    },
    data() {
        return {
            errMsg: null,
            expenses: [],
        };
    },

    methods: {},
    watch: {
        search(value) {
            axios
                .get(`api/expenses/search/${value}`)
                .then((res) => {
                    this.expenses = res.data;
                })
                .catch((err) => {
                    this.errMsg = err.message;
                });
        },
    },
};
</script>

<style lang="scss" scoped>
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
        flex-direction: column;
        justify-content: space-between;
        padding: 20px;
        border-bottom: 1px solid rgb(163 163 163);
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
                    .Allocate__Stock {
                        position: relative;
                        display: flex;
                        align-items: center;
                        gap: 20px;
                        .Icon {
                            height: 20px;
                            object-fit: contain;
                            cursor: pointer;
                        }
                    }
                }
            }
        }
    }
}
</style>
