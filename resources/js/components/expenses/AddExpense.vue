<template>
    <div class="Modal">
        <form @submit.prevent="addExpense">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1>
                    <strong style="text-transform: capitalize"
                    >Record Expense</strong
                    >
                </h1>
                <button @click="close">Close</button>
            </div>
            <div class="Input__Container">
                <label for="date">Date</label>
                <input name="date" type="date" v-model="date" required />
            </div>
            <div class="Input__Container">
                <label for="expense_on">Expense on</label>
                <input
                    name="expense_on"
                    v-model="expense_on"
                    type="text"
                    required
                />
            </div>
            <div class="Input__Container">
                <label for="amount">Amount (MKW)</label>
                <input name="amount" v-model="amount" type="number" required />
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

            <button>Add</button>
            <div v-if="errorMessage" class="text-red-400">
                {{ errorMessage }}
            </div>
        </form>
    </div>
</template>

<script>
import axios from "axios";
import { mapActions, mapGetters } from "vuex";
export default {
    emits: ["getExpenses", "closeModal"],
    data() {
        return {
            date: null,
            expense_on: "",
            amount: null,
            description: "",
            errorMessage: null,
            addedExpense: null,
        };
    },
    computed: {
        ...mapGetters(["userInfo"]),
    },
    methods: {
        ...mapActions(["changeLoading"]),
        addExpense() {
            this.changeLoading();
            axios
                .post("api/expenses/store", {
                    date: this.date,
                    expense_on: this.expense_on,
                    amount: this.amount,
                    description: this.description,
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
                    this.errorMessage = err.message;
                });
        },
        close(){
            this.$emit("closeModal")
        }
    },
};
</script>
