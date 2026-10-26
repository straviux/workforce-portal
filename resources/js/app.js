import './bootstrap';
import '../css/app.css';
import '../css/ios-design-system.css';

// Apply theme immediately from localStorage to prevent flash-of-wrong-theme
(() => {
	const saved = localStorage.getItem('theme') || 'system';
	document.documentElement.classList.toggle('dark', saved === 'dark');
})();

// Compensate for Windows 125% display scaling so the whole app renders at the
// same effective size as 100% scaling. Only applies while the device pixel
// ratio is 1.25, and reacts when the window moves between monitors with
// different scale factors. The .dpr-zoom class lets CSS undo the side effect
// on viewport-relative (vh/vw) lengths, which do not shrink with zoom.
(() => {
	const mq = window.matchMedia('(resolution: 1.25dppx)');
	const apply = () => {
		document.body.style.zoom = mq.matches ? '0.8' : '';
		document.documentElement.classList.toggle('dpr-zoom', mq.matches);
	};
	apply();
	mq.addEventListener('change', apply);
})();

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimeVue from 'primevue/config';
import Tooltip from 'primevue/tooltip';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import { definePreset } from '@primevue/themes';
import Aura from '@primevue/themes/aura';
import dayjs from 'dayjs';

// PrimeVue Components
import Button from 'primevue/button';
import Card from 'primevue/card';
import Panel from 'primevue/panel';
import AppDialog from '@/Components/AppDialog.vue';
import Drawer from 'primevue/drawer';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import InputIcon from 'primevue/inputicon';
import IconField from 'primevue/iconfield';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import Checkbox from 'primevue/checkbox';
import RadioButton from 'primevue/radiobutton';
import ToggleSwitch from 'primevue/toggleswitch';
import Tag from 'primevue/tag';
import Badge from 'primevue/badge';
import Chip from 'primevue/chip';
import Avatar from 'primevue/avatar';
import FileUpload from 'primevue/fileupload';
import Menu from 'primevue/menu';
import Toast from 'primevue/toast';
import Timeline from 'primevue/timeline';
import ProgressSpinner from 'primevue/progressspinner';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import Toolbar from 'primevue/toolbar';
import Popover from 'primevue/popover';
import ConfirmDialog from 'primevue/confirmdialog';
import FloatLabel from 'primevue/floatlabel';
import Password from 'primevue/password';
import Message from 'primevue/message';
import Editor from 'primevue/editor';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import safeHtmlDirective from '@/directives/safeHtml';
import ResponsibilityCenterSelect from '@/Components/selects/ResponsibilityCenterSelect.vue';
import ParticularsSelect from '@/Components/selects/ParticularsSelect.vue';
import AppIcon from '@/Components/ui/AppIcon.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Workforcify';

// Material Design 3 preset: user palette (primary #355c7d, secondary
// #6c5b7b, tertiary #c06c84, error #f67280, peach #f8b195) + MD3 shapes.
// The tonal scales mirror the Tailwind tokens in resources/css/app.css;
// the single seed variables live there (--md-*-seed).
const Md3 = definePreset(Aura, {
	primitive: {
		// MD3 corner shapes: xs 4, sm 8, md 12, lg 16, xl 28.
		borderRadius: {
			none: '0',
			xs: '4px',
			sm: '8px',
			md: '12px',
			lg: '16px',
			xl: '28px',
		},
	},
	semantic: {
		primary: {
			50: '#f5f7f8',
			100: '#e9edf1',
			200: '#d7dee5',
			300: '#b8c6d2',
			400: '#90a5b8',
			500: '#61809a',
			600: '#456987',
			700: '#355c7d',
			800: '#2b4b67',
			900: '#21394e',
			950: '#162735',
		},
		secondary: {
			50: '#f8f7f8',
			100: '#efedf0',
			200: '#e2dee5',
			300: '#ccc6d1',
			400: '#aea5b6',
			500: '#8c7f98',
			600: '#786886',
			700: '#6c5b7b',
			800: '#594b65',
			900: '#43384c',
			950: '#2d2634',
		},
		tertiary: {
			50: '#fcf8f9',
			100: '#f8eff1',
			200: '#f2e2e6',
			300: '#e9ccd4',
			400: '#dcaebb',
			500: '#ce8c9f',
			600: '#c5788e',
			700: '#c06c84',
			800: '#9d596c',
			900: '#774352',
			950: '#512d37',
		},
		error: {
			50: '#fff8f9',
			100: '#feeff1',
			200: '#fde3e6',
			300: '#fcced3',
			400: '#fab1b9',
			500: '#f8919c',
			600: '#f77d8a',
			700: '#f67280',
			800: '#ca5d69',
			900: '#99474f',
			950: '#673036',
		},
		peach: {
			50: '#fffbfa',
			100: '#fef6f3',
			200: '#feefea',
			300: '#fde4da',
			400: '#fbd4c5',
			500: '#fac2ac',
			600: '#f9b79d',
			700: '#f8b195',
			800: '#cb917a',
			900: '#9a6e5c',
			950: '#684a3f',
		},
		colorScheme: {
			light: {
				primary: {
					color: '#355c7d',
					inverseColor: '#ffffff',
					hoverColor: '#2b4b67',
					activeColor: '#21394e',
				},
				highlight: {
					background: '#fef6f3',
					focusBackground: '#feefea',
					color: '#684a3f',
					focusColor: '#684a3f',
				},
			},
			dark: {
				// Dark surface scale keeps the app's existing dark character.
				surface: {
					0: '#ffffff',
					50: '#f9fafb',
					100: '#f3f4f6',
					200: '#e5e7eb',
					300: '#d1d5db',
					400: '#9ca3af',
					500: '#6b7280',
					600: '#4b5563',
					700: '#374155',
					800: '#2a3040',
					900: '#222831',
					950: '#1a1e27',
				},
				primary: {
					color: '#d7dee5',
					inverseColor: '#21394e',
					hoverColor: '#e9edf1',
					activeColor: '#b8c6d2',
				},
				highlight: {
					background: 'rgba(254, 239, 234, .16)',
					focusBackground: 'rgba(254, 239, 234, .24)',
					color: 'rgba(255,255,255,.87)',
					focusColor: 'rgba(255,255,255,.87)',
				},
			},
		},
	},
	components: {
		// Buttons are rounded-xl (12px); dialogs use the 28px extra-large corner.
		button: {
			root: {
				borderRadius: '12px',
			},
		},
		dialog: {
			root: {
				borderRadius: '28px',
				// Uniform modal chrome: the card surface in both modes.
				// PrimeVue 4.5 paints only .p-dialog itself (dialog.background);
				// header, content and footer have no own background in its
				// stylesheet, so they inherit this root color — there is no
				// separate header strip color. The dark colorScheme entry
				// below overrides this light value when .dark is present.
				background: '#ffffff',
			},
			colorScheme: {
				light: {
					root: {
						background: '#ffffff',
					},
				},
				dark: {
					root: {
						background: '#2b3440',
					},
				},
			},
		},
		// MD3 cards use the 12px medium corner (Aura defaults them to xl/28px).
		card: {
			root: {
				borderRadius: '12px',
			},
		},
		// Bold palette: DataTable column-header row is slate blue with light
		// text in both modes. Hover/selected header-cell tokens are pinned to
		// the same colors so sortable/sorted headers do not revert to the
		// default light surface or highlight tint. Weight, padding and
		// borders are untouched (Aura defaults).
		datatable: {
			colorScheme: {
				light: {
					headerCell: {
						background: '#355C7D',
						color: '#ffffff',
						hoverBackground: '#355C7D',
						hoverColor: '#ffffff',
						selectedBackground: '#355C7D',
						selectedColor: '#ffffff',
					},
				},
				dark: {
					headerCell: {
						background: '#2b4b67',
						color: '#e9edf1',
						hoverBackground: '#2b4b67',
						hoverColor: '#e9edf1',
						selectedBackground: '#2b4b67',
						selectedColor: '#e9edf1',
					},
				},
			},
		},
		// Bold palette: the active tab's label/icon and its underline bar are
		// coral in light mode and pale coral in dark mode. activeBorderColor
		// is set to the same color so the tab's own bottom border does not
		// show the default slate-blue line under the coral active bar.
		tabs: {
			colorScheme: {
				light: {
					tab: {
						activeColor: '#F67280',
						activeBorderColor: '#F67280',
					},
					activeBar: {
						background: '#F67280',
					},
				},
				dark: {
					tab: {
						activeColor: '#fde3e6',
						activeBorderColor: '#fde3e6',
					},
					activeBar: {
						background: '#fde3e6',
					},
				},
			},
		},
	},
});

createInertiaApp({
	title: (title) => `${title} - ${appName}`,
	resolve: (name) =>
		resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
	setup({ el, App, props, plugin }) {
		const app = createApp({ render: () => h(App, props) })
			.use(plugin)
			.use(ZiggyVue)
			.use(PrimeVue, {
				ripple: true,
				theme: {
					preset: Md3,
					options: {
						prefix: 'p',
						darkModeSelector: '.dark',
						cssLayer: false,
					},
				},
			})
			.use(ToastService)
			.use(ConfirmationService);

		// Day.js for date handling across the app
		app.config.globalProperties.$dayjs = dayjs;
		// PrimeVue components
		app.component('Button', Button);
		app.component('Card', Card);
		app.component('Panel', Panel);
		// AppDialog wraps PrimeVue Dialog (stacked Esc handling); it is
		// registered under the plain `Dialog` name so call sites are untouched.
		app.component('Dialog', AppDialog);
		app.component('Drawer', Drawer);
		app.component('DataTable', DataTable);
		app.component('Column', Column);
		app.component('InputText', InputText);
		app.component('InputNumber', InputNumber);
		app.component('InputIcon', InputIcon);
		app.component('IconField', IconField);
		app.component('Textarea', Textarea);
		app.component('Select', Select);
		app.component('DatePicker', DatePicker);
		app.component('Checkbox', Checkbox);
		app.component('RadioButton', RadioButton);
		app.component('ToggleSwitch', ToggleSwitch);
		app.component('Tag', Tag);
		app.component('Badge', Badge);
		app.component('Chip', Chip);
		app.component('Avatar', Avatar);
		app.component('FileUpload', FileUpload);
		app.component('Menu', Menu);
		app.component('Toast', Toast);
		app.component('Timeline', Timeline);
		app.component('ProgressSpinner', ProgressSpinner);
		app.component('Tabs', Tabs);
		app.component('TabList', TabList);
		app.component('Tab', Tab);
		app.component('TabPanels', TabPanels);
		app.component('TabPanel', TabPanel);
		app.component('Toolbar', Toolbar);
		app.component('Popover', Popover);
		app.component('ConfirmDialog', ConfirmDialog);
		app.component('FloatLabel', FloatLabel);
		app.component('Password', Password);
		app.component('Message', Message);
		app.component('Editor', Editor);
		app.component('InputGroup', InputGroup);
		app.component('InputGroupAddon', InputGroupAddon);
		app.component('ResponsibilityCenterSelect', ResponsibilityCenterSelect);
		app.component('ParticularsSelect', ParticularsSelect);
		app.component('AppIcon', AppIcon);

		app.directive('tooltip', Tooltip);
		app.directive('safe-html', safeHtmlDirective);

		return app.mount(el);
	},
	progress: {
		delay: 250,
		color: '#29d',
		includeCSS: true,
		showSpinner: true,
	},
});

// Handle CSRF token expiry (419) for Inertia-driven requests.
router.on('invalid', (event) => {
	if (event.detail.response.status === 419) {
		event.preventDefault();
		window.axios
			.get('/sanctum/csrf-cookie')
			.then(() => window.axios.get('/csrf-token'))
			.then(({ data }) => {
				const meta = document.querySelector('meta[name="csrf-token"]');
				if (meta && data.token) meta.setAttribute('content', data.token);
			})
			.finally(() => router.reload());
	}
});
