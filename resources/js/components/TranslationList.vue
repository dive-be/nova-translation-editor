<template>
    <loading-view :loading="initialLoading">
        <heading class="mb-6">{{ __('Translations') }}</heading>
        <div class="flex">
            <div class="relative h-9 mb-6 flex-no-shrink">
                <icon
                    type="search"
                    class="absolute search-icon-center ml-3 text-70"
                />
                <input
                    class="appearance-none form-search w-search pl-search shadow"
                    placeholder="Search"
                    type="search"
                    v-model="search"
                    @keyup="performSearch"
                />
            </div>
            <div class="flex w-full justify-end">
                <button
                    @click.prevent="publishTranslations()"
                    class="cursor-pointer btn btn-default btn-primary"
                >
                    {{ __('Publish') }}
                </button>
            </div>
        </div>
        <card
            v-if="loading"
            class="flex flex-col items-center justify-center"
            style="min-height: 300px"
        >
            <loading-view></loading-view>
        </card>
        <card v-else class="flex flex-col items-center justify-center">
            <div class="border-b border-50 w-full p-4">
                <div class="flex items-center w-full space-x-4">
                    <div class="mr-4">
                        {{ __('Filter') }}
                    </div>
                    <select
                        @change="listTranslations()"
                        v-model="group"
                        class="block form-control-sm form-select max-w-sm"
                    >
                        <option value="">&lt; {{ __('Select a group') }} &gt;</option>
                        <option
                            v-for="translationGroup in translationGroups"
                            :value="translationGroup"
                        >{{ translationGroup }}</option>
                    </select>
                    <div class="ml-auto pl-4">
                        <a
                            class="ml-3 cursor-pointer font-bold text-80 text-sm"
                            v-for="locale in supportedLocales"
                            :key="locale"
                            :class="{'text-primary border-b-2 border-primary': locale === activeLocale}"
                            @click="selectLocale(locale)"
                        >
                            {{ locale }}
                        </a>
                    </div>
                </div>
            </div>
            <template v-if="translationData.length > 0">
                <table class="table w-full" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr class="text-left">
                            <th></th>
                            <th>{{ __('Group') }}</th>
                            <th>{{ __('Key') }}</th>
                            <th class="w-1/2">{{ __('Translation') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="translation in translationData"
                            v-bind:class="{unchanged: !translation.customized}"
                        >
                            <td>
                                <span v-if="translation.customized && !translation.published" class="rounded-full w-2 h-2 mx-2 inline-block bg-info" title="Published"></span>
                            </td>
                            <td>{{ translation.group }}</td>
                            <td>{{ translation.key }}</td>
                            <td>
                                <textarea class="w-full form-control form-input form-input-bordered py-3 h-auto my-3"
                                          rows="1" :placeholder="translation.placeholder" @change="saveTranslation(translation, $event)">{{ translation.text }}</textarea>
                            </td>
                            <td>
                                <button  v-if="translation.customized"
                                         @click.prevent="deleteTranslation(translation)"
                                         class="inline-flex appearance-none cursor-pointer text-70 hover:text-primary mr-3 has-tooltip">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="delete" role="presentation" class="fill-current"><path fill-rule="nonzero" d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"></path></svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </template>
            <div
                v-else
                style="min-height: 300px;"
                class="flex flex-col items-center justify-center"
            >
                <p>No translations found for these criteria.</p>
            </div>
        </card>
        <translation-publish-dialog
            v-if="publishDialog"
        ></translation-publish-dialog>
    </loading-view>
</template>

<script>
export default {
    mounted() {
        this.activeLocale = Nova.config.tool.default_locale;
        this.supportedLocales = Nova.config.tool.supported_locales;
        this.getTranslationGroups();
    },
    data() {
        return {
            initialLoading: true,
            loading: false,
            publishDialog: false,
            group: '',
            translationGroups: [],
            translationData: [],
            activeLocale: 'en',
            supportedLocales: null,
            search: '',
            previousSearch: '',
        };
    },
    methods: {
        getTranslationGroups() {
            axios
            .get(`/nova-vendor/nova-translation-editor/translations/groups`, {})
            .then(response => {
                this.initialLoading = false;
                this.translationGroups = response.data;
                this.listTranslations();
            })
            .catch(error => {
                this.loading = false;
                console.error(error);
            });
        },
        listTranslations() {
            this.$nextTick(async () => {
                this.loading = true;
                var params = {
                    search: this.search || null,
                    locale: this.activeLocale,
                    group: this.group || null
                };

                axios
                .get(`/nova-vendor/nova-translation-editor/translations/`, {
                    params: params,
                })
                .then(response => {
                    this.loading = false;
                    this.translationData = response.data;
                })
                .catch(error => {
                    this.loading = false;
                    console.error(error);
                });
            });
        },
        selectLocale(locale) {
            this.activeLocale = locale;
            this.listTranslations();
        },
        performSearch: _.debounce(function(event) {
            if (this.search != this.previousSearch) {
                this.previousSearch = this.search;
                this.listTranslations();
            }
        }, 200),
        saveTranslation(translation, event) {
            axios
            .post(`/nova-vendor/nova-translation-editor/translations/`, {
                id: translation.id,
                group: translation.group,
                key: translation.key,
                text: event.target.value,
                locale: this.activeLocale,
            })
            .then(response => {
                translation.id = response.data.id;
                translation.customized = response.data.customized;
                translation.published = response.data.published;

                Nova.success('Translation saved');
            })
            .catch(error => {
                Nova.error('Something went wrong while saving the translation');
                console.error(error);
            });
        },
        deleteTranslation(translation) {
            axios
            .delete(`/nova-vendor/nova-translation-editor/translations/` + translation.id, {})
            .then(response => {
                this.listTranslations();
                Nova.success('Translation deleted');
            })
            .catch(error => {
                Nova.error('Something went wrong while deleting the translation');
                console.error(error);
            });
        },
        publishTranslations() {
            axios
            .post(`/nova-vendor/nova-translation-editor/translations/publish`, {})
            .then(response => {
                this.listTranslations();
                Nova.success('Published all translations');
            })
            .catch(error => {
                Nova.error('Something went wrong while publishing the translations');
                console.error(error);
            });
        },
    },
};
</script>

<style>
tr.unused {
    background-color: #ffefef !important;
}
</style>
