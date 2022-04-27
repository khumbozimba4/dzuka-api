<template>
    <div class="Main__Wrap">
        <form @submit.prevent="editExpense">
            <input type="text" v-model="on" />
            <input type="text" v-model="date" />
            <input type="date" v-model="amount" />
            <textarea
                id="w3review"
                name="description"
                v-model="description"
                rows="4"
                cols="30"
            ></textarea>
            <button type="submit">Save</button>
        </form>
    </div>
</template>

<script>
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
        getExpense() {
            this.on = this.expense.expense_on;
            this.description = this.expense.description;
            this.amount = this.expense.amount;
            this.date = this.expense.date;
        },
        editExpense() {
            axios
                .patch(`api/expenses/${this.expense.id}/update`, {
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
                .catch((err) => {
                    this.errMsg = err.message;
                });
        },
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrap {
    position: absolute;
    top: 20px;
    right: 100px;
    background-color: #fff;
    padding: 20px;
    z-index: 99;
    form {
        display: flex;
        flex-direction: column;
        gap: 10px;

        input {
            width: 100%;
            padding: 10px;
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
