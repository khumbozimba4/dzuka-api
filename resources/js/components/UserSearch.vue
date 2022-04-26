<template>
    <div class="Contents__Container">
        <div class="Heading">
            <h1>Search Results</h1>
            <h3 v-if="users.length == 0">
                Oops! we cant find that user for you😢
            </h3>
        </div>
        <div class="Table__Container" v-if="users.length !== 0">
            <table class="Table">
                <thead class="Table__Head">
                    <tr class="Tr">
                        <td>UserID</td>
                        <td>Username</td>
                        <td>email</td>
                        <td>Role</td>
                    </tr>
                </thead>
                <tbody class="Table__Body">
                    <tr class="Tr" v-for="user in users" :key="user.id">
                        <td>
                            <strong>{{ user.id }}</strong>
                        </td>
                        <td>{{ user?.name }}</td>
                        <td>{{ user?.email }}</td>
                        <td>{{ user?.role }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import axios from "axios";
export default {
    props: ["search"],
    data() {
        return {
            errMsg: null,
            users: [],
        };
    },

    methods: {},
    watch: {
        search(value) {
            axios
                .get(`api/users/search/${value}`)
                .then((res) => {
                    this.users = res.data;
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
