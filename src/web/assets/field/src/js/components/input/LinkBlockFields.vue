<script>
import { h } from 'vue';
import { debounce } from 'lodash-es';

export default {
    name: 'LinkBlockFields',

    // Add nonsense as we don't want any Vue templating included in this template
    // Otherwise including Twig in field content will throw an error.
    delimiters: ['$!{', '}!$'],

    props: {
        template: {
            type: String,
            default: '',
        },
    },

    emits: ['update'],

    mounted() {
        this.$nextTick(() => {
            // If there was a fatal Vue error when rendering fields, there will be no `$el`.
            if (this.$el) {
                // Ensure any Craft fields are prepped.
                Craft.initUiElements(this.$el);

                this.$nextTick(() => {
                    // Watch all field content for changes to serialize them to our text inputs that are stored in JSON blocks.
                    this.watchFieldChanges();
                });
            }
        });
    },

    methods: {
        watchFieldChanges() {
            const updateFunction = debounce(this.emitUpdate, 50);

            // Use MutationObserver to detect _any_ change in the block, and be sure to debounce
            // calls as there are a lot of changes. Far more effective than all the hundreds of different
            // plugins, edge-cases and dynamic DOM elements we have to deal with to get this to work
            // "normally" and more efficiently by say checking input events. Notably, dealing with hidden
            // input change events, tag fields, ST/Matrix fields, and lots more.
            const observer = new MutationObserver((mutations) => {
                updateFunction();
            });

            observer.observe(this.$el, {
                childList: true,
                attributes: true,
                subtree: true,
                characterData: true,
            });

            // MutationObserver doesn't listen to value changes, so add handling for input events
            $(this.$el).on('input change', 'input, textarea, select', (e) => {
                this.emitUpdate();
            });
        },

        emitUpdate() {
            // Give it a second for the DOM to update, as we rely on DOM values to serialize.
            setTimeout(() => {
                this.$emit('update');
            }, 50);

            // Also, run this again at a longer timeout _just_ in case we've acted too quickly (Redactor).
            setTimeout(() => {
                this.$emit('update');
            }, 200);
        },
    },

    render() {
        // Apply our dynamically provided template, rendered via Craft.
        return h('div', {
            innerHTML: this.template,
        });
    },
};

</script>
