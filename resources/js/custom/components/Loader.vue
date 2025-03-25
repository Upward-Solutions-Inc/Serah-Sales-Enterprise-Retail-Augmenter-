<template>
    <div v-if="visible">
      <span class="spinner-bounce d-flex align-items-center justify-content-center">
        <span class="bounce1 mr-1" style="background-color: #38A4F8;"></span>
        <span class="bounce2 mr-1" style="background-color: #38A4F8;"></span>
        <span class="bounce3 mr-1" style="background-color: #38A4F8;"></span>
        <span class="bounce4" style="background-color: #38A4F8;"></span>
      </span>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      visible: {
        type: Boolean,
        default: true
      }
    }
  }
  </script>  