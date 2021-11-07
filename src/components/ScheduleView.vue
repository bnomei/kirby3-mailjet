<template>
  <k-inside>
    <k-view class="k-mailjet-schedule-view">
      <k-header>Mailjet Schedule</k-header>
      <k-items layout="cards" data-size="small">
        <k-item
          v-for="schedule in schedules"
          layout="cards"
          :key="schedule.value"
          :info="schedule.date"
          :text="schedule.name"
        />
      </k-items>
    </k-view>
  </k-inside>
</template>

<script>
export default {
  data() {
    return {
      schedules: [],
    };
  },
  created() {
    this.load();
  },
  methods: {
    load() {
      this.$api
        .get("mailjet/schedule/list")
        .then((response) => {
          this.schedules = response;
        })
        .catch((error) => {
          console.log(error);
        });
    },
  },
};
</script>

<style scoped></style>
