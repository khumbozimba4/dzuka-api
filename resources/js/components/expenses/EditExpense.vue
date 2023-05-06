<template>
    <div class="Modal">
        <form @submit.prevent="editExpense">
            <div
                style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                "
            >
                <h1>
                    <strong style="text-transform: capitalize"
                        >Edit Expense</strong
                    >
                </h1>
                <button @click="close">Close</button>
            </div>
            <div class="Input__Container">
                <label for="on">On</label>
                <input name="on" type="text" v-model="on" required />
            </div>
            <div class="Input__Container">
                <label for="date">Date</label>
                <input name="date" type="date" v-model="date" required />
            </div>
            <div class="Input__Container">
                <label for="date">Amount</label>
                <input name="amount" type="number" v-model="amount" required />
            </div>
            <div class="Input__Container">
                <label for="description">Description</label>
                <textarea
                    id="w3review"
                    name="description"
                    v-model="description"
                    rows="4"
                    cols="30"
                ></textarea>
            </div>
            <button type="submit">Save</button>
        </form>
    </div>
</template>

<script>
import { mapActions } from "vuex";
import {API} from "../../api";

export default {
    props: ["expense"],
    emits: ["getExpenses", "closeModal"],
    created() {
        this.getExpense();
    },
    data() {
        return {
            on: "",
            amount: "",
            date: null,
            description: "",
            errMsg: null,
        };
    },
    methods: {
        ...mapActions(["changeLoading"]),
        getExpense() {
            this.on = this.expense.expense_on;
            this.description = this.expense.description;
            this.amount = this.expense.amount;
            this.date = this.expense.date;
        },
        editExpense() {
            this.changeLoading();
                API.updateExpense(this.expense.id, {
                    amount: this.amount,
                    description: this.description,
                    expense_on: this.on,
                    date: this.date,
                })
                .then(() => {
                    this.$emit("closeModal");
                })
                .then(() => {
                    this.$emit("getExpenses");
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.changeLoading();
                    this.errMsg = err.message;
                });
        },
        close() {
            this.$emit("closeModal");
        },
    },
};
</script>
