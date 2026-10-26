<script setup>
import { computed } from 'vue';
import {
    ArrowLeft,
    ArrowRight,
    ArrowUpDown,
    Ban,
    Briefcase,
    Building,
    Calendar,
    CalendarClock,
    ChartColumn,
    Check,
    ChevronLeft,
    ChevronRight,
    CircleCheck,
    Clock,
    Database,
    Download,
    EllipsisVertical,
    Expand,
    Eye,
    EyeOff,
    FilePen,
    FileSpreadsheet,
    FilterX,
    History,
    House,
    IdCard,
    Image,
    Inbox,
    Info,
    ListChecks,
    LoaderCircle,
    Lock,
    LogOut,
    Maximize2,
    Menu,
    MessageSquare,
    Minus,
    Moon,
    Pencil,
    Plus,
    Printer,
    RefreshCw,
    Save,
    Search,
    Settings,
    Shield,
    SquarePen,
    Sun,
    Table,
    Tag,
    Trash2,
    Upload,
    User,
    UserPen,
    Users,
    X,
    Zap,
} from 'lucide-vue-next';

defineOptions({ inheritAttrs: false });

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    size: {
        type: [Number, String],
        default: 20,
    },
    strokeWidth: {
        type: [Number, String],
        default: 2,
    },
});

// Explicit static map: a bad name fails loudly at build time (tree-shaken
// imports) instead of silently rendering nothing at runtime.
const ICONS = {
    ArrowLeft,
    ArrowRight,
    ArrowUpDown,
    Ban,
    Briefcase,
    Building,
    Calendar,
    CalendarClock,
    ChartColumn,
    Check,
    ChevronLeft,
    ChevronRight,
    CircleCheck,
    Clock,
    Database,
    Download,
    EllipsisVertical,
    Expand,
    Eye,
    EyeOff,
    FilePen,
    FileSpreadsheet,
    FilterX,
    History,
    House,
    IdCard,
    Image,
    Inbox,
    Info,
    ListChecks,
    LoaderCircle,
    Lock,
    LogOut,
    Maximize2,
    Menu,
    MessageSquare,
    Minus,
    Moon,
    Pencil,
    Plus,
    Printer,
    RefreshCw,
    Save,
    Search,
    Settings,
    Shield,
    SquarePen,
    Sun,
    Table,
    Tag,
    Trash2,
    Upload,
    User,
    UserPen,
    Users,
    X,
    Zap,
};

const component = computed(() => {
    const icon = ICONS[props.name];

    if (!icon) {
        console.warn(`[AppIcon] Unknown icon name: "${props.name}"`);
    }

    return icon;
});
</script>

<template>
    <component
        :is="component"
        v-if="component"
        :size="size"
        :stroke-width="strokeWidth"
        v-bind="$attrs"
    />
</template>
