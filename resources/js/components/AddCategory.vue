<template>
    <div class="Main__Wrapper">
        <form>
            <input type="text" placeholder="Category Name" />
            <button type="submit">Save</button>
        </form>
    </div>
</template>

<script>
export default {};
</script>

<style lang="scss" scoped>
.Main__Wrapper {
    position: absolute;
    top: 20px;
    right: 50px;
    background-color: #fff;
    border-radius: 3px;
    padding: 10px 0px;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
        0 4px 6px -4px rgb(0 0 0 / 0.1);
    form {
        input {
            width: 100%;
            border: none;
            border-bottom: 1px solid rgb(212 212 212);
            font-size: 15px;
            outline: 0;
        }
        button {
            padding: 2px 10px;
            margin: 5px;
            border-radius: 5px;
            color: rgb(163 163 163);
            border: 1px solid rgb(212 212 212);

            &:hover {
                color: #fff;
                background-color: rgb(30 41 59);
            }
        }
    }
}
</style>
