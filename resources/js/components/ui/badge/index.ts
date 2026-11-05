import { cva, type VariantProps } from 'class-variance-authority'

export { default as Badge } from './Badge.vue'

export const badgeVariants = cva(
  'inline-flex items-center justify-center rounded-md border px-2 py-0.5 text-xs font-medium w-fit whitespace-nowrap shrink-0 [&>svg]:size-3 gap-1 [&>svg]:pointer-events-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive transition-[color,box-shadow] overflow-hidden',
  {
    variants: {
      variant: {
        default:
          'border-transparent bg-primary text-primary-foreground [a&]:hover:bg-primary/90',
        secondary:
          'border-transparent bg-secondary text-primary-text [a&]:hover:bg-secondary/90',
        outline:
          'text-primary-text [a&]:hover:bg-accent [a&]:hover:text-accent-foreground',
        success:
           'border-green-200 bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-200 dark:border-green-700',
        warning:
            'border-yellow-200 bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-200 dark:border-yellow-700',
        alert:
            'border-orange-200 bg-orange-100 text-orange-600 dark:bg-orange-900 dark:text-orange-200 dark:border-orange-700',
        destructive:
          'border-red-200 bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-200 dark:border-red-700',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
)
export type BadgeVariants = VariantProps<typeof badgeVariants>
