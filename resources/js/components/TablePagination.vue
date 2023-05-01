<template>
    <div class="wrapper" v-if="total > per_page">
        <div class="Action__Buttons">
            <button
                :class="!prev_page_url && 'disabled'"
                :disabled="!prev_page_url"
                @click="$emit('change', current - 1)"
            >
                PREV
            </button>
            <div class="Pages">
                <strong>{{ current }}</strong> /
                {{ Math.ceil(total / per_page) }}
            </div>
            <button
                :class="!next_page_url && 'disabled'"
                :disabled="!next_page_url"
                @click="$emit('change', current + 1)"
            >
                NEXT
            </button>
        </div>
    </div>
</template>

<script>
import { ChevronLeftIcon, ChevronRightIcon } from "@heroicons/vue/outline";

export default {
    name: "TablePagination",
    props: ["current", "total", "per_page", "next_page_url", "prev_page_url"],
    components: {
        ChevronLeftIcon,
        ChevronRightIcon,
    },
};
</script>

<style lang="scss" scoped>
.wrapper {
    background: white;
    margin-top: 15px;
    display: flex;
    align-items: center;

    .Action__Buttons {
        display: flex;
        align-items: center;
        gap: 10px;

        button {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            color: white;
            background: rgb(59 130 246);
            border-radius: 5px;
            font-weight: bold;
        }

        .Pages {
            color: gray;
            font-weight: bold;
        }

        .disabled {
            background: lightgray;
        }
    }

    .icon {
        width: 25px;
        object-fit: contain;
        cursor: pointer;
        color: #fff;
    }
}
</style>
