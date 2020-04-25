<template>
  <k-view class="k-mailjet-schedule-view">
    <k-header>Mailjet Schedule</k-header>
    <k-cards data-size="small">
      <k-card
        v-for="schedule in schedules"
        :key="schedule.value"
        :info="schedule.date"
        :text="schedule.name"
      />
    </k-cards>
  </k-view>
</template>

<script>
export default {
  name: "ScheduleView",
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
