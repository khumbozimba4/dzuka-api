<template>
    <div class="Contents__Container">
        <div class="Heading">
            <h1>Search Results</h1>
            <h3 v-if="categories.length == 0">
                Oops we cant find that category for you😢
            </h3>
        </div>
        <div class="Table__Container" v-if="categories.length !== 0">
            <table class="Table">
                <thead class="Table__Head">
                    <tr class="Tr">
                        <td>#</td>
                        <td>Product Category</td>
                        <td>Registered Products</td>
                        <td>View</td>
                    </tr>
                </thead>
                <tbody class="Table__Body">
                    <tr
                        class="Tr"
                        v-for="(category, index) in categories"
                        :key="category?.id"
                    >
                        <td>
                            <strong>{{ index + 1 }}</strong>
                        </td>
                        <td>{{ category?.category_name }}</td>
                        <td>{{ category?.products.length }}</td>
                        <td>
                            <ArrowNarrowRightIcon
                                class="Icon"
                                @click="gotoProducts(category)"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
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
            categories: [],
        };
    },

    methods: {
        gotoProducts(category) {
            this.$router.push({
                name: "products",
                params: {
                    categoryName: category.category_name,
                    category_id: category.id,
                },
            });
        },
    },
    watch: {
        search(value) {
            axios
                .get(`api/categories/search/${value}`)
                .then((res) => {
                    this.categories = res.data;
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
                    td {
                        .Icon {
                            height: 30px;
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
