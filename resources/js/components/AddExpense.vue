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
export default {
    emits: ["getExpenses", "closeAdd"],
    data() {
        return {
            date: null,
            expense_on: "",
            amount: null,
            description: "",
            errorMessage: null,
        };
    },
    methods: {
        addExpense() {
            axios
                .post("api/expenses/store", {
                    date: this.date,
                    expense_on: this.expense_on,
                    amount: this.amount,
                    description: this.description,
                })
                .then(() => {
                    this.$emit("closeAdd");
                })
                .then(() => {
                    this.$emit("getExpenses");
                })
                .catch((err) => {
                    this.errorMessage = err.message;
                });
        },
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrap {
    position: absolute;
    border-top: 1px solid gray;
    background: #fff;
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
