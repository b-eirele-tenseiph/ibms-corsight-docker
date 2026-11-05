import { cva, VariantProps } from 'class-variance-authority';

export { default as ModalFileViewer } from './ModalFileViewer.vue';
export { default as ModalForm } from './ModalForm.vue';
export { default as ModalShowForm } from './ModalShowForm.vue';

export const modalVariants = cva(
  'flex w-full flex-col border-0 px-5 ring-0 outline-none',
  {
    variants: {
      variant: {
        default:
                'max-h-[600px] sm:max-w-[600px]',
        medium:
                'max-h-[800px] sm:max-w-[800px]',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
)
export type ModalVariants = VariantProps<typeof modalVariants>