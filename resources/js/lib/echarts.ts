import themeDark from '@/themes/chart-dark.json';
import themeLight from '@/themes/chart-light.json';
import { BarChart, LineChart, MapChart, PieChart, ScatterChart } from 'echarts/charts';
import {
    GraphicComponent,
    GridComponent,
    LegendComponent,
    MarkAreaComponent,
    MarkLineComponent,
    MarkPointComponent,
    TitleComponent,
    ToolboxComponent,
    TooltipComponent,
    VisualMapComponent,
} from 'echarts/components';
import { registerTheme, use } from 'echarts/core';
import { CanvasRenderer, SVGRenderer } from 'echarts/renderers';
import type { App } from 'vue';
import { computed } from 'vue';
import ECharts, { LOADING_OPTIONS_KEY } from 'vue-echarts';

export const ECHARTS_PALETTES = {
    light: themeLight.color as string[],
    dark: themeDark.color as string[],
};

export default {
    install(app: App) {
        use([
            CanvasRenderer,
            SVGRenderer,
            BarChart,
            LineChart,
            ScatterChart,
            PieChart,
            MapChart,
            TitleComponent,
            MarkLineComponent,
            MarkAreaComponent,
            MarkPointComponent,
            TooltipComponent,
            ToolboxComponent,
            GraphicComponent,
            VisualMapComponent,
            GridComponent,
            LegendComponent,
        ]);

        registerTheme('light', themeLight);
        registerTheme('dark', themeDark);

        // Make isDark reactive
        const isDark = computed(() => document.documentElement.classList.contains('dark'));

        app.provide(
            LOADING_OPTIONS_KEY,
            computed(() => ({
                text: '',
                color: getComputedStyle(document.documentElement).getPropertyValue('--primary').trim(),
                maskColor: isDark.value ? 'rgba(0,0,0,0.4)' : 'rgba(255,255,255,0.8)',
                zlevel: 0,
            })),
        );

        app.provide('ECHARTS_PALETTES', ECHARTS_PALETTES);

        app.component('v-chart', ECharts);
    },
};
