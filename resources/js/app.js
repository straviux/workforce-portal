import './bootstrap';
import '../css/app.css';
import '../css/ios-design-system.css';

// Apply theme immediately from localStorage to prevent flash-of-wrong-theme
(() => {
	const saved = localStorage.getItem('theme') || 'system';
	document.documentElement.classList.toggle('dark', saved === 'dark');
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
import 'primeicons/primeicons.css';
import dayjs from 'dayjs';

// PrimeVue Components
import Button from 'primevue/button';
import Card from 'primevue/card';
import Panel from 'primevue/panel';
import Dialog from 'primevue/dialog';
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

const appName = import.meta.env.VITE_APP_NAME || 'Workforce Portal';

const Noir = definePreset(Aura, {
	semantic: {
		primary: {
			50: '{zinc.50}',
			100: '{zinc.100}',
			200: '{zinc.200}',
			300: '{zinc.300}',
			400: '{zinc.400}',
			500: '{zinc.500}',
			600: '{zinc.600}',
			700: '{zinc.700}',
			800: '{zinc.800}',
			900: '{zinc.900}',
			950: '{zinc.950}',
		},
		colorScheme: {
			light: {
				primary: {
					color: '{zinc.800}',
					inverseColor: '#ffffff',
					hoverColor: '{zinc.900}',
					activeColor: '{zinc.800}',
				},
				highlight: {
					background: '{zinc.950}',
					focusBackground: '{zinc.700}',
					color: '#ffffff',
					focusColor: '#ffffff',
				},
			},
			dark: {
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
					color: '{zinc.50}',
					inverseColor: '{zinc.950}',
					hoverColor: '{zinc.100}',
					activeColor: '{zinc.200}',
				},
				highlight: {
					background: 'rgba(250, 250, 250, .16)',
					focusBackground: 'rgba(250, 250, 250, .24)',
					color: 'rgba(255,255,255,.87)',
					focusColor: 'rgba(255,255,255,.87)',
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
					preset: Noir,
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
		app.component('Dialog', Dialog);
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
