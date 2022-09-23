// @ts-ignore
import React from "react"
import { Paragraph, styled, Subheader } from "@uvodohq/planum"

const Wrapper = styled("div", {
  pb: "$16"
})

export const PageDescription = ({ title, description }) => {
  return (
    <Wrapper>
      <Subheader css={{ color: "$textDark" }}>{title}</Subheader>
      <Paragraph css={{ color: "$textLight" }}>{description}</Paragraph>
    </Wrapper>
  )
}
