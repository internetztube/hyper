<template>
    <div class="hyper-wrapper" :class="{ 'no-header': !showHeader }" data-exclude-serialization>
        <div v-show="showHeader" class="hyper-header">
            <div class="hyper-header-type">
                <select v-model="link.handle" :disabled="settings.isStatic || settings.linkTypes.length < 2">
                    <option v-for="type in settings.linkTypes" :key="type.handle" :value="type.handle">{{ type.label }}</option>
                </select>
            </div>

            <div class="hyper-header-actions">
                <div v-if="settings.newWindow" class="hyper-header-new-window">
                    <lightswitch-field ref="switch" v-model="link.newWindow" :extra-small="true" :is-static="settings.isStatic">
                        <template #label>
                            <span class="hyper-header-new-window-label">{{ t('hyper', 'New Window') }}</span>
                        </template>
                    </lightswitch-field>
                </div>

                <template v-if="!settings.isStatic && (!settings.multipleLinks && linkType.tabCount > 1) || settings.multipleLinks">
                    <div v-if="settings.multipleLinks" class="hyper-header-settings ellipsis" @click.prevent="openSettings">
                        <svg fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M8 256a56 56 0 1 1 112 0A56 56 0 1 1 8 256zm160 0a56 56 0 1 1 112 0 56 56 0 1 1 -112 0zm216-56a56 56 0 1 1 0 112 56 56 0 1 1 0-112z" /></svg>
                    </div>

                    <div v-else class="hyper-header-settings" @click.prevent="openSettings">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512"><path d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336c44.2 0 80-35.8 80-80s-35.8-80-80-80s-80 35.8-80 80s35.8 80 80 80z" /></svg>
                    </div>
                </template>

                <div id="hyper-settings-template" class="hyper-menu" style="display: none;">
                    <ul>
                        <li v-if="linkType.tabCount > 1">
                            <a class="menu-item" role="option" tabindex="-1" @click.prevent="openSlideout">
                                <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512"><path d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336c44.2 0 80-35.8 80-80s-35.8-80-80-80s-80 35.8-80 80s35.8 80 80 80z" /></svg></span>
                                <span class="menu-item-label">{{ t('app', 'Settings') }}</span>
                            </a>
                        </li>
                    </ul>

                    <hr v-if="linkType.tabCount > 1" class="padded">

                    <ul>
                        <li>
                            <a class="menu-item error" role="option" tabindex="-1" @click.prevent="deleteBlock">
                                <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z" /></svg></span>
                                <span class="menu-item-label">{{ t('app', 'Delete') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <drag-handle v-if="settings.multipleLinks">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 944.1 945.2"><path d="M630.2,787.7c0-87-70.5-157.5-157.5-157.5s-157.5,70.5-157.5,157.5s70.5,157.5,157.5,157.5S630.2,874.7,630.2,787.7zM315.1,472.6c0-87-70.5-157.5-157.5-157.5S0,385.6,0,472.6s70.5,157.5,157.5,157.5S315.1,559.6,315.1,472.6z M630.2,157.5C630.2,70.5,559.6,0,472.6,0S315.1,70.5,315.1,157.5s70.5,157.5,157.5,157.5S630.2,244.5,630.2,157.5z M944.1,472.6c0-86.4-70-156.4-156.4-156.4s-156.4,70-156.4,156.4S701.3,629,787.7,629S944.1,559,944.1,472.6L944.1,472.6z" /></svg>
                </drag-handle>
            </div>
        </div>

        <link-block-fields v-if="fieldsHtml" ref="fields" class="hyper-body-wrapper" :template="fieldsHtml" @update="onFieldUpdate" />
    </div>
</template>

<script>
import { set, escapeRegExp, merge } from 'lodash-es';

import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';
import 'tippy.js/themes/light-border.css';

import { namespaceString } from '@utils/string';
import htmlize from '@utils/htmlize';

import LightswitchField from './settings/LightswitchField.vue';
import LinkBlockFields from './input/LinkBlockFields.vue';
import DragHandle from './input/DragHandle.vue';

export default {
    name: 'LinkBlock',

    components: {
        LightswitchField,
        LinkBlockFields,
        DragHandle,
    },

    props: {
        blockIndex: {
            type: Number,
            required: true,
            default: 0,
        },

        hyperField: {
            type: Object,
            default: () => { return {}; },
        },

        modelValue: {
            type: Object,
            default: () => { return {}; },
        },
    },

    emits: ['delete', 'update:modelValue'],

    data() {
        return {
            tippy: null,
            slideout: null,
            fieldsHtml: '',
            link: {},
        };
    },

    computed: {
        settings() {
            return this.hyperField.settings;
        },

        name() {
            return this.hyperField.name;
        },

        cacheKey() {
            return `${this.link.id}-${this.link.handle}`;
        },

        linkType() {
            return this.settings.linkTypes.find((linkType) => {
                return linkType.handle === this.link.handle;
            }) || {};
        },

        linkData() {
            const items = this.link;
            const result = {};

            // Filter out some properties that are render-only
            Object.keys(items).forEach((key) => {
                const item = items[key];

                if (key !== 'html' && key !== 'js') {
                    result[key] = item;
                }
            });

            return result;
        },

        showHeader() {
            if (this.settings.multipleLinks || this.settings.isStatic || this.settings.newWindow) {
                return true;
            }

            if (this.settings.linkTypes.length > 1) {
                return true;
            }

            if (this.linkType.tabCount > 1) {
                return true;
            }

            return false;
        },
    },

    watch: {
        'link.handle': function(newValue, oldValue) {
            if (oldValue) {
                // If we're switching link types, ensure that we cache the old field data before switching.
                // Switching back would show us outdated content for fields (the original content on page load).
                // Be sure to use the old cache key as well.
                this.cacheHtml(`${this.link.id}-${oldValue}`);
            }

            // Because the link handle is changed in `created()` this alao fires immediately.
            this.updateHtml();
            this.updateJs();
        },
    },

    created() {
        this.link.handle = this.settings.defaultLinkType;
        this.link = this.clone(this.modelValue);

        // Some important type-casting, where things can get messed up where fields are stored in a non-numerical-keyed array,
        // which isn't something I thought possible! This causes incorrect behvaiour when sending the values to element-slideout.
        // Using `set()` or `setWith()` won't change the property type from Array to Object. This is only an issue for empty arrays.
        // Because the link data is represented by JSON, it assumes things are an array, but they need to be an object.
        // https://github.com/verbb/hyper/issues/97
        if (Array.isArray(this.link.fields) && !this.link.fields.length) {
            this.link.fields = {};
        }

        if (Array.isArray(this.link.customAttributes) && !this.link.customAttributes.length) {
            this.link.customAttributes = {};
        }

        // Check if the currently selected link type is in the allowed types. If not, switch to the first available one.
        if (!(this.settings.linkTypes.map((linkType) => {
            return linkType.handle;
        }).includes(this.link.handle))) {
            // Save some settings to merge in, but not all. This is a new link type after all
            const oldId = this.link.id;

            this.link = this.clone(this.settings.linkTypes[0]);
            this.link.id = oldId;
        }
    },

    mounted() {
        this.$nextTick(() => {
            if (this.settings.multipleLinks) {
                this.initSettingsMenu();
            }
        });
    },

    methods: {
        getParsedLinkTypeHtml(html) {
            if (typeof html === 'string') {
                if (this.settings.isStatic) {
                    // Add a disabled attribute to everything is static
                    html = html.replace(/<(?:input|textarea|select)\s[^>]*/ig, '$& disabled');
                }

                return html.replace(new RegExp('__HYPER_BLOCK__', 'g'), this.link.id);
            }

            return '';
        },

        updateJs() {
            this.$nextTick(() => {
                // Add any JS required by fields
                let footHtml = this.hyperField.getCachedFieldJs(this.cacheKey);
                footHtml = this.getParsedLinkTypeHtml(footHtml);

                const $script = document.querySelector(`#hyper-${this.link.id}-script`);

                if (footHtml) {
                    // But first check if already output. Otherwise, multiple bindings!
                    if ($script) {
                        $script.parentElement.removeChild($script);
                    }

                    Craft.appendBodyHtml(footHtml);
                    Craft.initUiElements(this.$el);
                }
            });
        },

        updateHtml() {
            this.fieldsHtml = this.getParsedLinkTypeHtml(this.hyperField.getCachedFieldHtml(this.cacheKey));
        },

        cacheHtml(cacheKey = this.cacheKey) {
            // Before dragging this block, save a copy of the current DOM to the cache. We ue this to restore back
            // when finished moving. This is because Vue's rendering will not retain any edited non-Vue HTML.
            if (this.$refs.fields) {
                // Use `clone()` and `htmlize()` to properly copy existing DOM content
                const $fieldsHtml = $(this.$refs.fields.$el.childNodes).clone();

                // Special-case for Redactor. We need to reset it to its un-initialized form
                // because it doesn't have better double-binding checks.
                if ($fieldsHtml.find('.redactor-box').length) {
                    // Rip out the `textarea` which is all we need
                    const $textarea = $fieldsHtml.find('.redactor-box textarea').htmlize();
                    $fieldsHtml.find('.redactor-box').replaceWith($textarea);
                }

                // Special-case for Selectize. We need to reset it to its un-initialized form
                // because it doesn't have better double-binding checks.
                if ($fieldsHtml.find('.selectize').length) {
                    $fieldsHtml.find('.selectize').each((index, element) => {
                        // This is absolutely ridiculous. Selectize strips out `<option>` elements, so we can't
                        // fetch the original data from the DOM. Instead, find it in the original link type template.

                        // Get the original field HTML from it's `data-layout-element` which contains the UID
                        const fieldUid = $(element).parents('[data-type]').data('layout-element');

                        if (fieldUid) {
                            // Get the original HTML
                            const $newHtml = $(this.linkType.html).find(`[data-layout-element="${fieldUid}"] .selectize`);

                            if ($newHtml.length) {
                                // IDs and names will include placholders for Vizy, but if in a Matrix/Super Table field, will contain those
                                // which can't be easily replaced like Vizy placeholders can. So be sure to swap them back to what they were
                                $newHtml.find('select').attr('id', $(element).find('select').attr('id'));
                                $newHtml.find('select').attr('name', $(element).find('select').attr('name'));

                                // Restore any selected elements
                                $newHtml.find('select').val($(element).find('select').val());

                                // Replace the HTML with the altered original template
                                element.outerHTML = $newHtml.htmlize();
                            }
                        }
                    });
                }

                const $assetFields = $fieldsHtml.find('[data-type="craft\\\\fields\\\\Assets"]');

                // Prevent multiple "Upload files" buttons when re-rendering Assets fields
                if ($assetFields.length) {
                    $assetFields.each((index, element) => {
                        // Asset field's JS will create the button if required
                        $(element).find('[data-icon="upload"').remove();
                    });
                }

                const fieldsHtml = $fieldsHtml.htmlize();

                this.hyperField.setCachedFieldHtml(cacheKey, fieldsHtml);
            }
        },

        initSettingsMenu() {
            const $el = this.$el.parentElement;
            const $settingsBtn = $el.querySelector('.hyper-header-settings');
            const $template = $el.querySelector('#hyper-settings-template');

            if ($template && $settingsBtn) {
                $template.style.display = 'block';

                this.tippy = tippy($settingsBtn, {
                    content: $template,
                    trigger: 'click',
                    allowHTML: true,
                    arrow: true,
                    interactive: true,
                    appendTo: document.body,
                    placement: 'bottom',
                    theme: 'light-border hyper-tippy-menu',
                    maxWidth: '300px',
                    zIndex: 100,
                    hideOnClick: true,
                });
            }
        },

        openSettings() {
            if (!this.settings.multipleLinks) {
                this.openSlideout();
            }
        },

        openSlideout() {
            const params = {
                fieldId: this.settings.fieldId,
                blockIndex: this.blockIndex,
                data: JSON.stringify(this.linkData),
            };

            this.slideout = new Craft.CpScreenSlideout('hyper/fields/input-settings', { params });
            this.slideout.open();

            this.slideout.on('submit', (e) => {
                // Update the model with the data in the slideout
                Object.entries(e.response.data).forEach(([key, value]) => {
                    if (key === 'fields') {
                        Object.entries(value).forEach(([fieldKey, field]) => {
                            set(this.link, `${key}.${fieldKey}`, field);
                        });
                    } else {
                        this.link[key] = value;
                    }
                });

                this.$emit('update:modelValue', this.link);
            });

            if (this.tippy) {
                this.tippy.hide();
            }
        },

        deleteBlock() {
            if (this.tippy) {
                this.tippy.hide();
            }

            this.$emit('delete', this.blockIndex);
        },

        onFieldUpdate() {
            // We don't want to update serialized content on-load, because this can trigger a false-positive that something has changed
            // when there are nested fields. Instead, wait until we're okay to make changes (the field has been interacted with)
            if (!this.hyperField.listenForChanges) {
                return;
            }

            const postData = Garnish.getPostData(this.$refs.fields.$el);
            const content = Craft.expandPostArray(postData);

            // This will be in the format `hyperData[267267872][linkValue]...`, and for nested setups, it'll all be one level
            // so ensure that we grab the correct data for this block.
            const blockContent = content.hyperData[this.link.id] || [];

            this.link = merge(this.link, blockContent);

            this.$emit('update:modelValue', this.link);
        },
    },
};

</script>

<style lang="scss">

.hyper-wrapper {
    border: 1px solid #d8dee7;
    border-radius: 6px;
}

.hyper-header {
    padding: 0.35rem 0.75rem;
    border-radius: 6px 6px 0 0;
    display: flex;
    align-items: center;
    background-color: #f3f7fc;
    border-bottom: 1px solid #cdd9e4;
}

.hyper-wrapper.no-header {
    border: none;
    border-radius: 0;

    .hyper-body-wrapper {
        padding: 0.75rem 0;
    }
}

.hyper-header-type {
    position: relative;
    user-select: none;

    select {
        color: #667c92;
        font-size: 12px;
        border: 1px solid #cdd9e4;
        border-radius: 3px;
        padding: 3px 28px 3px 10px;
        background: #dee7ef;
        appearance: none;

        // Cannot use due to Safari serif bug
        // font-weight: 500;
    }

    &::after {
        position: absolute;
        content: '';
        top: 50%;
        right: 10px;
        width: 10px;
        height: 10px;
        transform: translateY(-50%);
        pointer-events: none;
        background: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9IiM2NjdjOTIiIHZpZXdCb3g9IjAgMCA1MTIgNTEyIj48cGF0aCBkPSJNMjMzLjQgNDA2LjZjMTIuNSAxMi41IDMyLjggMTIuNSA0NS4zIDBsMTkyLTE5MmMxMi41LTEyLjUgMTIuNS0zMi44IDAtNDUuM3MtMzIuOC0xMi41LTQ1LjMgMEwyNTYgMzM4LjcgODYuNiAxNjkuNGMtMTIuNS0xMi41LTMyLjgtMTIuNS00NS4zIDBzLTEyLjUgMzIuOCAwIDQ1LjNsMTkyIDE5MnoiPjwvcGF0aD48L3N2Zz4=");
    }
}

.hyper-header-type-icon {
    display: inline-flex;
    width: 10px;
    height: 10px;
    margin-left: 0.5rem;
    color: #78838e;

    svg {
        width: 100%;
        height: 100%;
    }
}

.hyper-header-actions {
    margin-left: auto;
    display: flex;
    align-items: center;
}

.hyper-header-new-window {
    display: flex;
    align-items: center;
    user-select: none;
}

.hyper-header-new-window-label {
    color: #667c92;
    font-size: 12px;
    font-weight: 500;
    padding-right: 0.5rem;
    cursor: pointer;
    display: block;
    margin-top: -2px;
}

.hyper-header-settings,
.hyper-header-move {
    display: inline-flex;
    width: 12px;
    height: 12px;
    margin-left: 0.75rem;
    color: #78838e;
    cursor: pointer;

    svg {
        width: 100%;
        height: 100%;
    }

    &.ellipsis {
        width: 16px;
        height: 16px;
    }
}

.hyper-header-move {
    cursor: move;
}

.hyper-body-wrapper > .flex-fields {
    align-content: flex-start;
    display: flex;
    flex-wrap: wrap;
    margin: 0 calc(var(--row-gap)*-1) calc(var(--row-gap)*-1);
    width: calc(100% + var(--row-gap)*2);

    // Duplicate Craft styles so we can append blocks to the body when dragging and not mess up styles
    @media only screen and (min-width: 1535px) {
        > :not(h2):not(hr):not(.line-break).width-25,
        > :not(h2):not(hr):not(.line-break).width-50,
        > :not(h2):not(hr):not(.line-break):last-child.width-25,
        > :not(h2):not(hr):not(.line-break):last-child.width-50 {
            width: 50%;
        }
    }

    > :not(h2):not(hr):not(.line-break),
    > :not(h2):not(hr):not(.line-break):last-child {
        position: relative;
        width: 100%;
    }

    > * {
        box-sizing: border-box;
        margin: 0 0 var(--row-gap)!important;
        padding: 0 var(--row-gap);
    }
}

// Required to properly override `!important`
#content :not(.meta) .hyper-body-wrapper > .flex-fields > *,
.hyper-body-wrapper > .flex-fields > * {
    margin-bottom: 1rem !important;
}

#content :not(.meta).hyper-body-wrapper > .flex-fields > :not(h2):not(hr):not(.line-break):before,
.hyper-body-wrapper > .flex-fields > :not(h2):not(hr):not(.line-break):before {
    display: none;
}

.hyper-body-wrapper {
    display: flex;
    gap: 1rem;
    padding: 0.75rem 0.75rem;
    background: #fff;
    border-radius: 0 0 6px 6px;

    .flex-fields {
        --row-gap: 0.5rem !important;

        margin-bottom: -1rem !important;
    }

    .flex-fields > * {
        .copytextbtn.small {
            padding: 0 5px;
        }

        .copytextbtn.small .copytextbtn__value {
            font-size: .6rem;
        }

        .copytextbtn .copytextbtn__icon {
            margin-top: -2px;
            padding: 0;
            width: 8px;
            font-size: 9px;
        }

        .heading {
            font-size: 12px;
            margin-bottom: 4px;

            label {
                font-weight: 600;
            }
        }

        .instructions {
            font-size: 12px;
            line-height: 1;
            margin-top: -0.2rem;
            margin-bottom: 0.5rem;
        }
    }

    .status-badge {
        display: none;
    }
}

[v-cloak] {
    display: none;
}

</style>
