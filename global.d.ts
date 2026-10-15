declare const acf: any;
declare const jQuery: any;
declare const $: any;

declare module "*.scss" {
  const content: { [className: string]: string };
  export default content;
}
