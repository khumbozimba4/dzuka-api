<template>
    <div class="Main__Wrap">
        <form @submit.prevent="addExpense">
            <h1>Record Expense</h1>
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
    emits: ["getExpenses", "closeAdd"],
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
                .then((res) => {
                    this.addedExpense = res.data;
                })
                .then(() => {
                    this.$emit("closeAdd");
                })
                .then(() => {
                    this.$emit("getExpenses");
                })
                .then(() => {
                    axios.post("api/transactions/store", {
                        user_id: this.userInfo.id,
                        transaction_name: "Record Expenses",
                        description: `Added expense Id: ${this.addedExpense.id} Name: ${this.addedExpense.expense_on}`,
                    });
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.changeLoading();
                    this.errorMessage = err.message;
                });
        },
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrap {
    position: absolute;
    z-index: 99;
    border-top: 1px solid gray;
    background: #fff;
    width: 400px;
    top: 50px;
    right: 200px;
    border-radius: 5px;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
        0 4px 6px -4px rgb(0 0 0 / 0.1);

    form {
        padding: 20px;
        h1 {
            font-weight: 800;
        }
        .Input__Container {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            input {
                background: none;
                outline: none;
                border-bottom: 1px solid gray;
                color: gray;
            }
        }
        button {
            padding: 5px 10px;
            background-color: rgb(30 41 59);
            color: #fff;
            border-radius: 3px;
            margin-top: 10px;

            &:hover {
                background: rgb(15 23 42);
            }
        }
    }
}
</style>
