const MODE_MAP: Record<string, string> = { '1': 'Auto', '2': 'Cool', '3': 'Dry', '4': 'Fan' };
const FAN_MAP: Record<string, string> = { auto: 'Auto', '1': 'Low', '2': 'Med', '3': 'High' };

const title = (s: string) => s.replace(/[_\s]+/g, ' ').replace(/\w\S*/g, (w) => w[0].toUpperCase() + w.slice(1).toLowerCase());

export const formatDeviceCommand = (c: Record<string, any> | undefined): string => {
    if (!c) return '';
    const cmd = String(c.command ?? '').trim();
    const param = c.parameter;

    // 1) turnOn / turnOff / turnOnS1 / turnOffS2
    const mSwitch = cmd.match(/^turn(on|off)(?:S(\d+))?$/i);
    if (mSwitch) return mSwitch[2] ? `${title(mSwitch[1])} S${mSwitch[2]}` : title(mSwitch[1]);

    // 2) powerState & powerOn/powerOff & ON/OFF variants
    if (/^power(state|on|off)$/i.test(cmd)) {
        const v = typeof param === 'string' ? param : cmd.replace(/power|state/gi, '');
        return /on/i.test(v) ? 'On' : /off/i.test(v) ? 'Off' : title(v);
    }
    if (/^(on|off)$/i.test(cmd)) return title(cmd);
    if (/^power(on|off)$/i.test(cmd)) return title(cmd.replace(/^power/i, ''));

    // 3) Fan speed
    const mFanInline = cmd.match(/^fanSpeed(?:auto|(\d+))$/i);
    if (mFanInline) return `Fan ${FAN_MAP[(mFanInline[1] ?? 'auto').toLowerCase()] ?? title(mFanInline[1] ?? 'auto')}`;
    if (/^fanSpeed$/i.test(cmd)) return `Fan ${FAN_MAP[String(param ?? '').toLowerCase()] ?? title(String(param ?? ''))}`.trim();

    // 4) Mode
    if (/^mode$/i.test(cmd)) return `Mode ${title(MODE_MAP[String(param ?? '')] ?? String(param ?? ''))}`;

    // 5) Temperature
    if (/^(temperature|changeTemperature)$/i.test(cmd)) {
        const v = typeof param === 'object' && param?.new_setpoint != null ? param.new_setpoint : param;
        return v == null || v === '' ? '' : `${v} °C`;
    }

    // 6) Position
    if (/^setPosition$/i.test(cmd)) {
        const last =
            typeof param === 'string'
                ? (param
                      .split(',')
                      .reverse()
                      .find((p) => /^\d+$/.test(p.trim())) ?? '')
                : typeof param === 'number'
                  ? String(param)
                  : '';
        return last ? `${last}%` : '';
    }

    // 7) TV/Projector IR
    if (/^[A-Z ]+$/.test(cmd)) return title(cmd);

    // 8) Fallbacks
    if (param == null || param === '') return title(cmd);
    if (typeof param !== 'object') {
        if (/temp|temperature|new_setpoint/i.test(String(cmd))) return `${param} °C`;
        if (typeof param === 'string' && param.includes(',')) {
            const last = param
                .split(',')
                .reverse()
                .find((p) => /^\d+$/.test(p.trim()));
            if (last) return `${last}%`;
        }
        return String(param);
    }

    return Array.isArray(param)
        ? param.join(', ')
        : Object.entries(param)
              .map(([k, v]) => (/temp|temperature|new_setpoint/i.test(k) ? `${v} °C` : String(v)))
              .join(', ');
};
