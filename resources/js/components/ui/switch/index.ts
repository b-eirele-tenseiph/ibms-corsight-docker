import type { VariantProps } from 'class-variance-authority'
import { cva} from 'class-variance-authority'

export { default as Switch } from './Switch.vue'

export const switchVariants = cva(
    'peer inline-flex shrink-0 cursor-pointer items-center rounded-full border border-transparent shadow-xs transition-all focus-visible:border-ring focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=unchecked]:bg-input',
    {
      variants: {
        size: {
        '2xl': 'w-[4rem] h-[1.5rem]',
        xl: 'w-[3.5rem] h-[1.40rem]',
        lg: 'w-[3rem] h-[1.25rem]',
        base: 'w-[2.5rem] h-[1.25rem]',
        sm: 'w-[2rem] h-[1.15rem]',
    },
},
},
)

export const switchThumbVariants = cva(
  [
   'bg-white pointer-events-none block h-5 w-5 p-px rounded-full shadow-lg ring-0 transition-transform data-[state=unchecked]:translate-x-px'
  ],
  {
    variants: {
        size: {
        '2xl': 'size-5 data-[state=checked]:translate-x-[2.60rem]',
        xl: 'size-5 data-[state=checked]:translate-x-[2.10rem]',
        lg: 'size-4 data-[state=checked]:translate-x-[1.80rem]',
        base: 'size-4 data-[state=checked]:translate-x-[1.30rem]',
        sm: 'size-3.5 data-[state=checked]:translate-x-[0.93rem]',
      },
    },
  },
)

export type SwitchVariantsProps = VariantProps<typeof switchVariants>
export type SwitchThumbVariantsProps = VariantProps<typeof switchThumbVariants>
