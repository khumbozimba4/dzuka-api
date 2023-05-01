<template>
    <div class="Main__Wrapper">
        <ConfirmDelete
            @toggleCancel="toggleCancel"
            @toggleDelete="toggleDelete"
            v-if="confirmDelete"
        />
        <div class="NavBar__Container">
            <div class="Title">
                <UserCircleIcon class="Icon" />
                <p>Users</p>
            </div>
            <div class="Search__Bar">
                <input
                    type="text"
                    class="Input"
                    placeholder="Search..."
                    v-model="search"
                />
                <SearchIcon class="Search__Icon" />
            </div>
            <div class="Options"></div>
        </div>

        <div class="Contents__Container">
            <div class="Heading">
                <div class="Right__Side">
                    <div class="Add__Category" @click="changeRegisterModal">
                        Create User
                    </div>
                </div>
            </div>
            <div class="Table__Container">
                <table class="Table">
                    <thead class="Table__Head">
                        <tr class="Tr">
                            <td>UserID</td>
                            <td>Username</td>
                            <td>Email</td>
                            <td>Role</td>
                            <td v-if="userInfo.role !== finance">Actions</td>
                        </tr>
                    </thead>
                    <tbody class="Table__Body">
                        <tr
                            class="Tr"
                            v-for="(user, index) in list"
                            :key="user.id"
                        >
                            <td>
                                <strong>{{ user.id }}</strong>
                            </td>
                            <td>{{ user.name }}</td>
                            <td>{{ user.email }}</td>
                            <td>{{ user.role.name }}</td>
                            <td class="Icons" v-if="userInfo.role !== finance">
                                <PencilIcon
                                    class="Icon"
                                    @click="toggleEditUser(user)"
                                />
                                <TrashIcon
                                    class="Icon Icon_Delete"
                                    @click="toggleDeleteUser(user.id)"
                                />
                                <EditUser
                                    :user="user"
                                    @getUsers="getUsers"
                                    @closeModal="editUserOpen = false"
                                    v-if="
                                        editUserOpen &&
                                        selected === list[index]
                                    "
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <TablePagination
                    v-if="list.length"
                    :current="users.current_page"
                    :total="users.total"
                    :per_page="users.per_page"
                    :prev_page_url="users.prev_page_url"
                    :next_page_url="users.next_page_url"
                    @change="(value) => getUsers(value)"
                />
            </div>
        </div>
        <div v-if="errorMessage">{{ errorMessage }}</div>
    </div>
</template>

<script>
import {
    SearchIcon,
    UserCircleIcon,
    PencilIcon,
    TrashIcon,
} from "@heroicons/vue/outline";
import { mapActions, mapGetters } from "vuex";
import EditUser from "../components/user/EditUser.vue";
import ConfirmDelete from "../components/ConfirmDelete.vue";
import axios from "axios";
import { API } from "../api";
import TablePagination from "../components/TablePagination.vue";
export default {
    components: {
    ConfirmDelete,
    SearchIcon,
    UserCircleIcon,
    PencilIcon,
    TrashIcon,
    EditUser,
    TablePagination
},
    data() {
        return {
            isOpen: false,
            errorMessage: "",
            totalAmount: 0,
            users: null,
            list:[],
            search: "",
            editUserOpen: false,
            selected: null,
            confirmDelete: false,
            deletedItem: null,
        };
    },
    created() {
        this.getUsers();
        this.setFinanceVariable();
    },
    computed: {
        ...mapGetters(["userAdded", "userInfo"]),
    },
    watch: {
        userAdded(value) {
            if (value == true) {
                this.getUsers();
            }
        },
    },
    methods: {
        ...mapActions(["changeRegisterModal", "changeLoading"]),
        setFinanceVariable() {
            this.finance = "finance";
        },
        getUsers(page) {
            this.changeLoading();
            API.listUsers(page)
                .then(({data}) => {
                    this.users = data;
                    this.list = data.data;
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.changeLoading();
                    console.log(err);
                });
        },
        toggleEditUser(user) {
            this.selected = user;
            this.editUserOpen = !this.editUserOpen;
        },
        toggleDeleteUser(id) {
            this.deletedItem = id;
            this.confirmDelete = true;
        },
        toggleDelete() {
            this.changeLoading();
            axios
                .delete(`api/users/${this.deletedItem}`)
                .then(() => {
                    this.confirmDelete = false;
                })
                .then(() => {
                    this.getUsers();
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
            position: relative;
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
